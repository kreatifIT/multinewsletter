<?php
// Gruppen
$query = 'SELECT id, name  '.
		'FROM '. rex::getTablePrefix() .'375_group '.
		'ORDER BY name';
$result = rex_sql::factory();
$result->setQuery($query);
$num_rows = $result->getRows();

$group_ids = [];
for($i = 0; $i < $num_rows; $i++) {
	$group_ids[$result->getValue("id")] = $result->getValue("name");
	$result->next();
}
print '<p>Welche Gruppen sollen vom Nutzer abonniert werden können? Wenn nur eine '
	.'Gruppe markiert ist, wird dem Nutzer keine Auswahl angeboten.<br /></p>';
$select_feature = new rex_select(); 
$select_feature->setName('REX_INPUT_VALUE[1][]'); 
$select_feature->setMultiple(true); 
$select_feature->setSize(10);
$select_feature->setAttribute('class', 'form-control');

// Daten
foreach($group_ids as $group_ids => $name)  {
  $select_feature->addOption($name, $group_ids); 
}

// Vorselektierung
$features_selected = rex_var::toArray("REX_VALUE[1]");
if(is_array($features_selected)) {
	foreach($features_selected as $group_id) {
		$select_feature->setSelected($group_id);
	}
}

echo $select_feature->show();
?>
<br>
<p>Texte, Bezeichnungen bzw. Übersetzugen werden im <a href="<?php print rex_url::backendPage('multinewsletter/config'); ?>">Multinewsletter Addon</a> verwaltet.</p>