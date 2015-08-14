package com.saasplex.rm.utils

import com.saasplex.rm.*
import com.saasplex.rm.adapters.*
import org.apache.log4j.*

public class S5InstallScript extends ScriptContext {

	private static final def LOG = Logger.getLogger(S5InstallScript.class)
	def installerUrl
	def dbScriptUrl
	def cfgFileUrl
	def cfgFileName = "config_inc.php"
	def appName = "mantisbt"

	def install() {
    
		installerUrl = variables["installerUrl"]
		dbScriptUrl = variables["dbScriptUrl"]
		cfgFileUrl = variables["cfgFileUrl"]
        
		def domainName = getVar("domainName")
		if(!domainName) throw new Exception("Missing required parameter domainName")

		//get the servers
		def mysql = new Mysql() //default constructor chooses a agent
		def agent = mysql.agent
		//def apache = new Apache2(agent) // we wnat to have both things in the same box
		def apache1 = new Apache2(agent) 
         	def apache2 = Apache2Synchronized.getInstance() 

		def os = new Linux(agent)
		def fs = new FileSystem(agent)

		//get a unique identifier and a server
		String uid = randomString(15)
		def db
		def ip
		def app
		def appPath

		// Set the url of the resource
		resource.description = "mantisbt"
		resource.url = "https://" + domainName

		ip = agent.getIp()
		
		// ------- Building the DB -------
		try {
		
			db = new DataBase() //this represents a database with its users and its recipe for creating tables,etc

			/*
			* Truncating the database name if it exceeds 40 characters. 
			* 
			* MySQL has limitations for Database/Table name length to 65 
			* bytes.
			*/

			def databaseName = uid+'_'+appName
			if (databaseName.length() > 40) {
				databaseName = databaseName.substring(0,40)
			}

			db.setName(databaseName.replaceAll(/\W/, "_"))
			//db.setScript(dbScriptUrl)
			def dbUsername = "mbt" + randomString(7)
			def dbPassword = randomString(10)
			db.addUser(dbUsername, dbPassword) //mysql username limited to 16 chars. will truncate and delete/create so careful
			mysql.createDataBase(db)
			//set the variables needed for replacing values in the cfg template
			readVars(db)

			// ------- Building the web app -------
			try {
		
				def osUsername = "mbt" + randomString(7)
				setParameter("osUsername", osUsername)
				variables.put("osUsername", osUsername)

				def osPassword = randomString(10)
				setParameter("osPassword", osPassword)
				variables.put("osPassword", osPassword)

				def osGroupname = "${osUsername}Group"
				setParameter("osGroupname", osGroupname)
				variables.put("osGroupname", osGroupname)

				os.createGroup(osGroupname)
				os.createUser(osUsername, osPassword, osGroupname)

				appPath = "/home/jail/home/$osUsername/$uid"
				app = new PhpApp() // represents a web app to be deployed on a web server,subclass of WebApp
				app.server = apache1 //we asing the appserver so when reading vars the Server based variables will be there
				app.name = appName
				app.domains = [domainName]
				app.sourceUrl = installerUrl
				app.path = appPath
				app.logName = "$uid" 
				app.addConfigFile(cfgFileName, cfgFileUrl)
				app.setHttpMode(app.HTTPS_ONLY)
				// set the variables needed for replacing values in the cfg template,
				//  and sets parameters associated with this resource
				readVars(app)
				app.variables = getVariables()
				resource.agent = agent
			
				try {
			
					//apache.createWebApp(app)
					apache2.createWebApp(app, resource)
					fs.chown(appPath, "$osUsername:$osGroupname")
					fs.chmod(appPath, '700')
				
				} catch (e) {
					LOG.error("Create Web Failed. So rolling back")
					// apache.removeApp(app)
					apache2.removeApp(app, resource)
					throw e
				}
				
			} catch(e) {
			
				LOG.error("Rolling Back Unix user creation")
				os.deleteUser(getParameter("osUsername"))
				os.deleteGroup(getParameter("osGroupname"))
				throw e
				
			}
			
		} catch(e) {
		
			LOG.error("Rolling Back DB creation")
			mysql.dropDataBase(db)
			LOG.error("DB Rolled Back")
			throw e
			
		}
		
	}

	def uninstall() {
    
        def server = resource.agent

        //def apache2 = new Apache2(server)
	def apache1 = new Apache2(server)
        def apache2 = Apache2Synchronized.getInstance()  

        def mysql = new Mysql(server)
        def os = new Linux(server)
        def domainName = getParameter("webAppDomain")
        def osUsername = getParameter("osUsername")
	def osGroupname = getParameter("osGroupname")
        
        def app = new PhpApp()
        app.name = appName
        app.domains = [domainName]
        app.path = getParameter("webAppPath")
        app.setHttpMode(getParameter("webAppHttpMode"))
        app.server = apache1
        apache2.removeApp(app, resource)

        mysql.dropDataBase(getParameter("dbName"))
        mysql.deleteUser(getParameter("dbUser"))
        
        os.deleteUser(osUsername)
        os.deleteGroup(osGroupname)
        
	try {

		def cmd = "/opt/removeJailedUserSettings.sh -u ${osUsername}"
		def result = server.execute(cmd)
		LOG.debug "Result : $result"

	} catch (Exception e) {
		LOG.error "Error while deleting user from passwd file. $e"
	}

        setStatus("uninstalled")
        
    }

}

