<?php
require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/plugins/KnowledgeBase/KnowledgeBase.php' );

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
		<h1>Knowledge Base</h1>
	</div>
	<div class="table-responsive">
		<table class="table">
			<thead>
				<tr>
					<th> Summary </th>
					<th> Last Updated </th>
					<th> Category </th>
				</tr>
			</thead>

			<?php $t_close_issues = KnowledgeBasePlugin::getClosedIssues(); ?>

			<tbody>	
				<?php foreach ($t_close_issues as $t_bug):?>	
					<tr>
						<td><a href="view.php?id=<?php echo $t_bug->id; ?>"><?php echo $t_bug->summary ?></a></td>
						<td>
							<?php
								echo date( config_get( 'normal_date_format' ), $t_bug->last_updated );
							?>
						</td>
						<td>
							<?php 
								echo string_display_line( category_full_name( $t_bug->category_id ) );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

</body>
</html>