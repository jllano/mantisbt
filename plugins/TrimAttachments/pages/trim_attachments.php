<?php
#
# Copyright (c) 2016 MantisHub
# Licensed under the MIT license
#

require_once( 'core.php' );

access_ensure_global_level( ADMINISTRATOR );

layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin();

$t_closed_threshold = config_get( 'bug_closed_status_threshold' );
$t_query = 'SELECT bf.bug_id bug_id, sum(bf.filesize) size from {bug_file} bf LEFT JOIN {bug} b ON b.id = bf.bug_id WHERE b.status >= ' . db_param() . ' GROUP BY bug_id ORDER BY size DESC';
$t_result = db_query( $t_query, array( $t_closed_threshold ) );
?>

<div class="col-md-6 col-xs-6">
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-paperclip"></i>
				<?php echo plugin_lang_get( 'title' ) ?>
		</h4>
	</div>
<div class="widget-body dz-clickable">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed">

<?php
while ( $t_row = db_fetch_array( $t_result ) ) {
	echo '<tr><td>', string_get_bug_view_link( $t_row['bug_id'] ), '</td><td>' . number_format( $t_row['size'] ) . '</td></tr>';
}
?>

</table>
</div>
</div>
</div>
</div>
</div>

<?php
layout_page_end( __FILE__ );
    