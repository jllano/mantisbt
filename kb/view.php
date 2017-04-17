<?php
require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

$f_bug_id = gpc_get_int( 'id' );

bug_ensure_exists( $f_bug_id );

$t_bug = bug_get( $f_bug_id, true );

?>


<html dir="ltr" lang="en-US"><head>
	<meta charset="utf-8">
	<!-- v11709 -->
	<title>MantisHub</title>
  
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

</head>

<body>

<!-- Fixed navbar -->
<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <div id="navbar" class="collapse navbar-collapse">
      <ul class="nav navbar-nav">
        <li class="active"><a href="#">MantisHub</a></li>
      </ul>
    </div><!--/.nav-collapse -->
  </div>
</nav>
<br/><br/>
<div class="container">
	<div class="page-header">
		<h1><?php echo $t_bug->summary; ?></h1>
		<p class="lead">Modified on: <?php echo date( config_get( 'normal_date_format' ), $t_bug->last_updated ); ?></p>
	</div>
	
	<p> 
		<?php echo $t_bug->description; ?>
	</p>	

</div>

</body>
</html>