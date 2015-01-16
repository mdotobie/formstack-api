<?php
	
	require_once '../lib/FormstackApi.php';
	
	$fs = new FormstackApi('8dbb5a26dc9adf77d9c636d0c7d26623');
	
	var_dump( $fs );
	
?>

<h1>Formstack API</h1>

<h2>Available Resources</h2>

<?php
	
	$forms = $fs->getForms();
	
	$options = array();
	
	foreach( $forms as $form ) {
		
		$options[] = '<option value="' . $form->id . '">' . $form->name . '</option>';
	}
	
	//var_dump( $options );
	
?>

<select><?php echo implode( $options ); ?></select>

<ul>
	<li>Forms</li>
	<li>Fields</li>
	<li>Submissions</li>
</ul>