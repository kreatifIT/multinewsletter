<?php
$func     = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');

// Eingabeformular
if ($func == 'edit') {
    $form = rex_form::factory(rex::getTablePrefix() . '375_archive', rex_i18n::msg('multinewsletter_menu_archive'), "id = " . $entry_id, "post", false);

    $query_archive  = "SELECT * FROM " . rex::getTablePrefix() . "375_archive WHERE id = " . $entry_id;
    $result_archive = rex_sql::factory();
    $result_archive->setQuery($query_archive);

    // Sprach ID
    $sprache = "(" . $result_archive->getValue("clang_id") . ")";
    if (rex_clang::exists($result_archive->getValue("clang_id"))) {
        $sprache = rex_clang::get($result_archive->getValue("clang_id"))->getName() . " " . $sprache;
    }
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_language'), $sprache));

    // Betreff
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_subject'), html_entity_decode($result_archive->getValue("subject"))));

    // Inhalt
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_htmlbody'), '<a href="' . rex_url::currentBackendPage() . '&func=shownewsletter&shownewsletter=' . $entry_id . '" target="_blank">' . rex_i18n::msg('multinewsletter_archive_output_details') . '</a>'));

    // Empfänger
    $recipients = [];
	if (strpos($result_archive->getValue("recipients"), '|') !== FALSE) {
		$recipients = preg_grep('/^\s*$/s', explode("|", $result_archive->getValue("recipients")), PREG_GREP_INVERT);
	}
	else if (strpos($result_archive->getValue("recipients"), ',') !== FALSE) {
		$recipients = preg_grep('/^\s*$/s', explode(",", $result_archive->getValue("recipients")), PREG_GREP_INVERT);
	}
    $recipients_html = '<div style="font-size: 0.75em; width: 100%; max-height: 400px; overflow:auto; background-color: white; padding:8px;"><table width="100%"><tr>';
    foreach ($recipients as $key => $recipient) {
        $recipients_html .= "<td width='33%'>" . strtolower($recipient) . "</td>";
        if ($key > 1 && $key % 3 == 2) {
            $recipients_html .= "</tr><tr>";
        }
    }
    $recipients_html .= "</tr></table></div>";
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_recipients_count'), count($recipients)));
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_recipients'), $recipients_html));

    // E-Mailadresse Absender
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_group_default_sender_email'), $result_archive->getValue("sender_email")));

    // Empfänger Gruppen
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_groupname'), $result_archive->getValue("group_ids")));

    // Name Absender
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_group_default_sender_name'), $result_archive->getValue("sender_name")));

    // Erstellungsdatum
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_newsletter_preparedate'), $result_archive->getValue("setupdate")));

    // Sendedatum
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_sentdate'), $result_archive->getValue("sentdate")));

    // Redaxo Benutzer vom Versand
    $form->addRawField(raw_field(rex_i18n::msg('multinewsletter_archive_redaxo_sender'), $result_archive->getValue("sentby")));

    if ($func == 'edit') {
        $form->addParam('entry_id', $entry_id);
    }

    $form->show();

    print '<br><style type="text/css">' . '#rex-375-archive-archiv-save, #rex-375-archive-archiv-apply {visibility:hidden}' . '</style>';
}
// Newsletter anzeigen
else if ($func == 'shownewsletter') {
    // Zuerst bisherige Ausgabe von Redaxo löschen
    ob_end_clean();
    header_remove();

    $query_archive  = "SELECT * FROM " . rex::getTablePrefix() . "375_archive " . "WHERE id = " . filter_input(INPUT_GET, 'shownewsletter', FILTER_VALIDATE_INT);
    $result_archive = rex_sql::factory();
    $result_archive->setQuery($query_archive);

    print base64_decode($result_archive->getValue("htmlbody"));
    exit;
}
// Eintrag löschen
else if ($func == 'delete') {
    $query  = "DELETE FROM " . rex::getTablePrefix() . "375_archive " . "WHERE id = " . $entry_id;
    $result = rex_sql::factory();
    $result->setQuery($query);

    echo rex_view::success(rex_i18n::msg('multinewsletter_archive_deleted'));
    $func = '';
}

// Übersichtsliste
if ($func == '') {
    $list = rex_list::factory('SELECT id, subject, clang_id, sentdate FROM ' . rex::getTablePrefix() . '375_archive ORDER BY sentdate DESC');
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-backup"></i>';
    $list->addColumn('', $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams('', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->setColumnLabel('id', rex_i18n::msg('id'));
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">###VALUE###</td>']);

    $list->setColumnLabel('subject', rex_i18n::msg('multinewsletter_archive_subject'));
    $list->setColumnParams('subject', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->setColumnLabel('clang_id', rex_i18n::msg('multinewsletter_newsletter_clang'));
    $list->setColumnParams('clang_id', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->setColumnLabel('sentdate', rex_i18n::msg('multinewsletter_archive_sentdate'));
    $list->setColumnParams('sentdate', ['func' => 'edit', 'entry_id' => '###id###']);

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###id###']);

    $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###id###']);
    $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('confirm_delete_module'));

    $list->setNoRowsMessage(rex_i18n::msg('multinewsletter_group_not_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('multinewsletter_group'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}