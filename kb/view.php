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

<div class="page-header">
  <h1><?php echo $t_bug->summary; ?></h1>
</div>

<div>
	
	
		
</div>

</body>
</html>