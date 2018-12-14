<?php
$sql = rex_sql::factory();
if (rex_sql_table::get(rex::getTable('375_archive'))->hasColumn('archive_id')) {
	// Migrate Redaxo 4 tables
	$sql->setQuery('ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `archive_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` ADD `article_id` INT(11) NOT NULL AFTER `id`;
		UPDATE `' . rex::getTablePrefix() . '375_archive` SET `clang_id` = (`clang_id` + 1);
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `htmlbody` `htmlbody` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` ADD `attachments` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `htmlbody`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive`
			ADD COLUMN `setupdate_new` datetime DEFAULT NULL AFTER `setupdate`,
			ADD COLUMN `sentdate_new` datetime DEFAULT NULL AFTER `sentdate`;
		UPDATE `' . rex::getTablePrefix() . '375_archive`
			SET `setupdate_new` = FROM_UNIXTIME(`setupdate`),
				`sentdate_new` = FROM_UNIXTIME(`sentdate`);
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` DROP INDEX setupdate;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive`
			DROP `setupdate`,
			DROP `sentdate`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `setupdate_new` `setupdate` DATETIME NOT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive` CHANGE `sentdate_new` `sentdate` DATETIME NOT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_archive`
			ADD UNIQUE KEY `setupdate` (`setupdate`,`clang_id`);	

		ALTER TABLE `' . rex::getTablePrefix() . '375_group` CHANGE `group_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
		ALTER TABLE `' . rex::getTablePrefix() . '375_group` ADD `mailchimp_list_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `default_article_id`;

		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `user_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `send_archive_id` `send_archive_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` ADD `mailchimp_list_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `send_archive_id`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user`
			ADD COLUMN `createdate_new` datetime NULL DEFAULT NULL AFTER `createdate`,
			ADD COLUMN `activationdate_new` datetime NULL DEFAULT NULL AFTER `activationdate`,
			ADD COLUMN `updatedate_new` datetime NULL DEFAULT NULL AFTER `updatedate`;
		UPDATE `' . rex::getTablePrefix() . '375_user`
			SET `createdate_new` = FROM_UNIXTIME(`createdate`),
				`activationdate_new` = FROM_UNIXTIME(`activationdate`),
				`updatedate_new` = FROM_UNIXTIME(`updatedate`);
		ALTER TABLE `' . rex::getTablePrefix() . '375_user`
			DROP `createdate`,
			DROP `activationdate`,
			DROP `updatedate`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `createdate_new` `createdate` DATETIME NULL DEFAULT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `activationdate_new` `activationdate` DATETIME NULL DEFAULT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `updatedate_new` `updatedate` DATETIME NULL DEFAULT NULL;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` ADD `privacy_policy_accepted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `activationkey`;
		ALTER TABLE `' . rex::getTablePrefix() . '375_user` CHANGE `activationkey` `activationkey` VARCHAR(45) NULL DEFAULT NULL;
		UPDATE `' . rex::getTablePrefix() . '375_user` SET `clang_id` = (`clang_id` + 1);');
	$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_archive ENGINE = INNODB;');
	$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_group ENGINE = INNODB;');
	$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_user ENGINE = INNODB;');
}
else {
	// Create
	$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_archive` (
		`id` int(11) unsigned NOT NULL auto_increment,
		`article_id` int(11) NOT NULL,
		`clang_id` int(11) NOT NULL,
		`subject` varchar(255) NOT NULL,
		`htmlbody` longtext NOT NULL,
		`attachments` text NULL DEFAULT NULL,
		`recipients` longtext NOT NULL,
		`group_ids` text NOT NULL,
		`sender_email` varchar(255) NOT NULL,
		`sender_name` varchar(255) NOT NULL,
		`setupdate` DATETIME NULL DEFAULT NULL,
		`sentdate` DATETIME NULL DEFAULT NULL,
		`sentby` varchar(255) NOT NULL,
	PRIMARY KEY(`id`),
	UNIQUE KEY `setupdate` (`setupdate`, `clang_id`)
	) ENGINE=INNODB DEFAULT CHARSET=utf8;');
	$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_group` (
		`id` int(11) unsigned NOT NULL auto_increment,
		`name` varchar(255) NOT NULL,
		`default_sender_email` varchar(255) NOT NULL,
		`default_sender_name` varchar(255) NOT NULL,
		`default_article_id` int(11) unsigned NOT NULL,
		`mailchimp_list_id` varchar(100) NULL,
		`createdate` DATETIME NULL DEFAULT NULL,
		`updatedate` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY(`id`),
	UNIQUE KEY `name` (`name`)
	) ENGINE=INNODB DEFAULT CHARSET=utf8;');
	$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_user` (
		`id` int(11) unsigned NOT NULL auto_increment,
		`email` varchar(255) NOT NULL,
		`grad` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
		`firstname` varchar(255) NOT NULL,
		`lastname` varchar(255) NOT NULL,
		`title` tinyint(4) NOT NULL,
		`clang_id` int(11) NOT NULL,
		`status` tinyint(1) NOT NULL,
		`group_ids` text NOT NULL,
		`send_archive_id` int(11) unsigned NOT NULL,
		`mailchimp_id` varchar(100) NULL,
		`createdate` DATETIME NULL DEFAULT NULL,
		`createip` varchar(45) NOT NULL,
		`activationdate` DATETIME NULL DEFAULT NULL,
		`activationip` varchar(45) NOT NULL,
		`activationkey` varchar(45) NOT NULL,
		`updatedate` DATETIME NULL DEFAULT NULL,
		`updateip` varchar(45) NOT NULL,
		`subscriptiontype` varchar(16) NOT NULL,
		`privacy_policy_accepted` TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY(`id`),
	UNIQUE KEY `email` (`email`)
	) ENGINE=INNODB DEFAULT CHARSET=utf8;');
}

// Standartkonfiguration erstellen
if (!$this->hasConfig()) {
    $this->setConfig('sender', '');
    $this->setConfig('link', 0);
    $this->setConfig('link_abmeldung', 0);
    $this->setConfig('max_mails', 15);
    $this->setConfig('versandschritte_nacheinander', 20);
    $this->setConfig('sekunden_pause', 305);
    $this->setConfig('lang_fallback', 1);
    $this->setConfig('default_test_anrede', 0);
    $this->setConfig('default_test_email', rex::getProperty('ERROR_EMAIL'));
    $this->setConfig('default_test_vorname', 'Max');
    $this->setConfig('default_test_nachname', 'Mustermann');
    $this->setConfig('default_test_article', rex_article::getSiteStartArticleId());
    $this->setConfig('default_test_article_name', '');
    $this->setConfig('default_test_sprache', $default_clang_id);
    $this->setConfig('subscribe_meldung_email', '');
}
