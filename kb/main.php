<?php
require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/plugins/KnowledgeBase/KnowledgeBase.php' );

?>

<html dir="ltr" lang="en-US"><head>
  <meta charset="utf-8">
  <!-- v11709 -->
  <title>MantisHub</title>
</head>

<body>

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
					<td><?php echo $t_bug->summary ?></td>
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

</body>
</html>