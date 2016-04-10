<?php
$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');

// Eingabeformular
if ($func == 'edit' || $func == 'add') {
	$form = rex_form::factory(rex::getTablePrefix() .'375_group', rex_i18n::msg('multinewsletter_group'), "group_id = ". $entry_id, "post", FALSE);

	// Gruppenname
	$field = $form->addTextField('name');
	$field->setLabel(rex_i18n::msg('multinewsletter_group_name'));

	// Absender E-Mailadresse
	$field = $form->addTextField('default_sender_email');
	$field->setLabel(rex_i18n::msg('multinewsletter_group_default_sender_email'));

	// Gruppenname
	$field = $form->addTextField('default_sender_name');
	$field->setLabel(rex_i18n::msg('multinewsletter_group_default_sender_name'));

	// Artikel ID
	$field = $form->addLinkmapField('default_article_id');
	$field->setLabel(rex_i18n::msg('multinewsletter_group_default_article_id'));

	if($func == 'edit') {
		$form->addParam('entry_id', $entry_id);
	}

	$form->show();
	
	print "<br>";
}
// Eintrag löschen
else if ($func == 'delete') {
	$query = "DELETE FROM ". rex::getTablePrefix() ."375_group "
		."WHERE group_id = ". $entry_id;
	$result = rex_sql::factory();
	$result->setQuery($query);
	
	echo rex_view::error(rex_i18n::msg('multinewsletter_delete'));
	$func = '';
}

if ($func == '') {
    $list = rex_list::factory('SELECT group_id, name FROM '. rex::getTablePrefix() .'375_group ORDER BY name ASC');
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-module"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('create_module'), 'add') . ' title="' . rex_i18n::msg('multinewsletter_hinzufuegen') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###group_id###']);

    $list->setColumnLabel('group_id', rex_i18n::msg('multinewsletter_group_id'));
    $list->setColumnLayout('group_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('multinewsletter_group_name'));
    $list->setColumnParams('name', ['func' => 'edit', 'entry_id' => '###group_id###']);

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###group_id###']);

    $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###group_id###']);
    $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('confirm_delete_module'));

    $list->setNoRowsMessage(rex_i18n::msg('multinewsletter_group_not_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('multinewsletter_group'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
?>