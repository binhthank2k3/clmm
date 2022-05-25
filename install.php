<?php
    require_once(__DIR__."/config/config.php");
    require_once(__DIR__."/config/function.php");

   
    // INSERT DATABASE CHO BẢN CẬP NHẬT

    $CMSNT->query("CREATE TABLE `accounts_momo` (
        `id` int(11) NOT NULL,
        `sdt` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL,
        `password` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL,
        `token` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL,
        `status` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL,
        `max` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci ");
    $CMSNT->query("ALTER TABLE `accounts_momo` ADD PRIMARY KEY (`id`) ");
    $CMSNT->query("ALTER TABLE `accounts_momo` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT ");
    $CMSNT->query("ALTER TABLE `accounts_momo` ADD `name` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL AFTER `max`");
    $CMSNT->query("ALTER TABLE `accounts_momo` ADD `money` int(11) COLLATE utf8_vietnamese_ci NOT NULL AFTER `name`");
    
    $CMSNT->query("CREATE TABLE `bang_xep_hang` (
        `id` int(11) NOT NULL,
        `sdt` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL,
        `amount` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci ");
    $CMSNT->query("ALTER TABLE `bang_xep_hang` ADD PRIMARY KEY (`id`) ");
    $CMSNT->query("ALTER TABLE `bang_xep_hang` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT ");





    $CMSNT->query("CREATE TABLE `pay_momo` (
        `id` int(11) NOT NULL,
        `account` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL,
        `sdt` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL,
        `name` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL,
        `money` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_vietnamese_ci ");
    $CMSNT->query("ALTER TABLE `pay_momo` ADD PRIMARY KEY (`id`) ");
    $CMSNT->query("ALTER TABLE `pay_momo` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT ");
    $CMSNT->query("ALTER TABLE `pay_momo` ADD `createdate` DATETIME NOT NULL AFTER `money` ");
    $CMSNT->query("ALTER TABLE `pay_momo` ADD `noidung` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL AFTER `money`");    
    $CMSNT->query("ALTER TABLE `pay_momo` ADD `transId` varchar(255) COLLATE utf8_vietnamese_ci NOT NULL AFTER `noidung`");        

    if(!$CMSNT->get_row("SELECT * FROM `options` WHERE `name` = 'status_hoan_tien_chan_le' "))
    {
        $CMSNT->insert("options", [
            'name'   => 'status_hoan_tien_chan_le',
            'value' => 'ON'
        ]);
    }
    // INSERT views
    if(!$CMSNT->get_row("SELECT * FROM `options` WHERE `name` = 'views' "))
    {
        $CMSNT->insert("options", [
            'name'   => 'views',
            'value' => 0
        ]);
    }
    // INSERT show_statistics
    if(!$CMSNT->get_row("SELECT * FROM `options` WHERE `name` = 'show_statistics' "))
    {
        $CMSNT->insert("options", [
            'name'   => 'show_statistics',
            'value' => 'ON'
        ]);
    }
    // INSERT script_live_chat
    if(!$CMSNT->get_row("SELECT * FROM `options` WHERE `name` = 'script_live_chat' "))
    {
        $CMSNT->insert("options", [
            'name'   => 'script_live_chat',
            'value' => ''
        ]);
    }
    if(!$CMSNT->get_row("SELECT * FROM `options` WHERE `name` = 'show_top' "))
    {
        $CMSNT->insert("options", [
            'name'   => 'show_top',
            'value' => 'ON'
        ]);
    }
    if(!$CMSNT->get_row("SELECT * FROM `options` WHERE `name` = 'noidung_chanle' "))
    {
        $CMSNT->insert("options", [
            'name'   => 'noidung_chanle',
            'value' => 'Trả thưởng phiên '
        ]);
    }