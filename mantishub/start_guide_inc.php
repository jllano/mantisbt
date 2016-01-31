<?php
$t_active_step = mantishub_guide_stage();
if ( $t_active_step === false ) {
    return;
}
?>
<div id="get_started_guide" class="widget-box widget-color-dark">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-power-off"></i>
            Getting Started Guide
        </h4>
    </div>

    <div class="widget-body">
        <div class="widget-main">

            <p class="lead">Welcome to MantisHub</p>

            <p>A good place to start is to watch our getting started video.
            This helps you get started with MantisHub and perform the most
            important tasks.</p>

            <div id="getting-started-video" style="margin-bottom: 20px;">
                <?php # TODO: maintain aspect ratio: 500 x 281 ?>
                <iframe src="https://player.vimeo.com/video/149359701" width="100%" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
            </div>

            <p>Now let's take a couple of minutes to get this done for your MantisHub.
                Once you complete all the tasks in this guide, you will acquire the essential knowledge to use MantisHub
                to manage your own projects and teams.  This guide will also disappear once steps are completed.</p>

            <div class="panel-group accordion-style1 accordion-style2" id="step-list-1">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a class="accordion-toggle <?php echo $t_active_step == MANTISHUB_GUIDE_PROJECT ? '' : 'collapsed' ?>" data-toggle="collapse" data-parent="#step-list-1" href="#step-1-1">
                            <i data-icon-show="ace-icon fa fa-chevron-left" data-icon-hide="ace-icon fa fa-chevron-down" class="pull-right ace-icon fa <?php echo $t_active_step == 1 ? 'fa-chevron-down' : 'fa-chevron-left' ?>"></i>
                            <i class="ace-icon fa fa-plus bigger-130"></i>
                            &nbsp; Create your projects
                        </a>
                    </div>
                    <div id="step-1-1" class="panel-collapse collapse <?php echo $t_active_step == MANTISHUB_GUIDE_PROJECT ? 'in' : ''  ?>" style="height: auto;">
                        <div class="panel-body">
                            We have already pre-created your first project and called it 'MyProject'.
                            However, if you would like to create more projects,
                            you can create them as follows:
                            <div class="space-4"></div>
                            <ul>
                                <li>Click on 'Manage' on the sidebar menu</li>
                                <li>Click on 'Manage Projects' at the top menu bar</li>
                                <li>Click on 'Create New Project' and enter the project name</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a class="accordion-toggle <?php echo $t_active_step == MANTISHUB_GUIDE_CATEGORY ? '' : 'collapsed' ?>" data-toggle="collapse" data-parent="#step-list-1" href="#step-1-2">
                            <i data-icon-show="ace-icon fa fa-chevron-left" data-icon-hide="ace-icon fa fa-chevron-down" class="ace-icon fa pull-right <?php echo $t_active_step == 2 ? 'fa-chevron-down' : 'fa-chevron-left' ?>"></i>
                            <i class="ace-icon fa fa-sitemap"></i>
                            &nbsp; Create new categories
                        </a>
                    </div>
                    <div id="step-1-2" class="panel-collapse collapse <?php echo $t_active_step == MANTISHUB_GUIDE_CATEGORY ? 'in' : ''  ?>">
                        <div class="panel-body">
                            Projects grow big. Sooner or later you will need to get a bit organized by classifying
                            issues into different categories. This will help your team filter to only issues in their area
                            of interest. For the 'MyProject' project, let's create three categories:
                            'Website', 'Backend', 'Mobile'
                            <div class="space-4"></div>
                            <ul>
                                <li>Click on 'Manage' on the sidebar menu</li>
                                <li>Click on 'Manage Projects' at the top menu bar</li>
                                <li>Click on the 'MyProject' project name</li>
                                <li>Scroll down to 'Categories' section</li>
                                <li>Enter 'Website' in the edit box and click on 'Add Category'</li>
                                <li>Repeat for 'Backend' &amp; 'Mobile' categories</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a class="accordion-toggle <?php echo $t_active_step == MANTISHUB_GUIDE_BUG ? '' : 'collapsed'  ?>" data-toggle="collapse" data-parent="#step-list-1" href="#step-1-3">
                            <i data-icon-show="ace-icon fa fa-chevron-left" data-icon-hide="ace-icon fa fa-chevron-down" class="ace-icon fa pull-right <?php echo $t_active_step == 3 ? 'fa-chevron-down' : 'fa-chevron-left' ?>"></i>
                            <i class="ace-icon fa fa-bug bigger-130"></i>
                            &nbsp; Report your first issue
                        </a>
                    </div>
                    <div id="step-1-3" class="panel-collapse collapse <?php echo $t_active_step == MANTISHUB_GUIDE_BUG ? 'in' : ''  ?>">
                        <div class="panel-body">
                            You should be all set to create an issue or bug report against the 'MyProject' project you created
                            earlier:
                            <div class="space-4"></div>
                            <ul>
                                <li>Click on 'Report Issue' on the sidebar menu</li>
                                <li>Select 'Website' as the issue category</li>
                                <li>Fill the summary &amp; description fields</li>
                                <li>Click 'Submit Report' at the bottom of the form</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a class="accordion-toggle <?php echo $t_active_step == MANTISHUB_GUIDE_USER ? '' : 'collapsed'  ?>" data-toggle="collapse" data-parent="#step-list-1" href="#step-1-4">
                            <i data-icon-show="ace-icon fa fa-chevron-left" data-icon-hide="ace-icon fa fa-chevron-down" class="ace-icon fa pull-right <?php echo $t_active_step == 4 ? 'fa-chevron-down' : 'fa-chevron-left' ?>"></i>
                            <i class="ace-icon fa fa-group bigger-130"></i>
                            &nbsp; Invite team members
                        </a>
                    </div>

                    <div id="step-1-4" class="panel-collapse collapse <?php echo $t_active_step == MANTISHUB_GUIDE_USER ? 'in' : ''  ?>">
                        <div class="panel-body">
                            Open issues need to be assigned to someone (i.e. developer, tester, support, etc) to drive it
                            to resolution. Let's invite other team members to MantisHub:
                            <div class="space-4"></div>
                            <ul>
                                <li>Click on 'Manage' on the sidebar menu</li>
                                <li>Click on 'Manage Users' at the top menu bar</li>
                                <li>Click on 'Create New Account' button</li>
                                <li>Complete the form and click 'Create User'</li>
                                An email notification will be sent to the person you invited and they will get access
                                to MantisHub.
                            </ul>
                            <div class="space-4"></div>
                            This step concludes the Getting Started guide. Congratulations!
                            For more information and how-to documents, please visit MantisHub
                            <a href="http://www.mantishub.com/docs/">documentation</a>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="space-10"></div>

<script src="/javascript/mantishub.js"></script>