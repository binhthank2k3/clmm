<?php
    require_once("../config/config.php");
    require_once("../config/function.php");
    require_once("../config/chanle.php");
    error_reporting(1);

    foreach($CMSNT->get_list(" SELECT DISTINCT `partnerId` FROM `momo` ") as $row)
    {
        $total_money = $CMSNT->get_row(" SELECT SUM(`amount`) FROM `momo` WHERE `partnerId` = '".$row['partnerId']."' ")['SUM(`amount`)'];
        $partnerId = $row['partnerId'];
        if($CMSNT->get_row(" SELECT * FROM `bang_xep_hang` WHERE `sdt` = '".$row['partnerId']."' "))
        {
            $CMSNT->update('bang_xep_hang', [
                'amount' => $total_money
            ], " `sdt` = '".$row['partnerId']."' ");
        }
        else
        {
            $CMSNT->insert("bang_xep_hang", [
                'sdt'   => $partnerId,
                'amount'  => $total_money
            ]);
        }
    }

    foreach($CMSNT->get_list("SELECT * FROM `accounts_momo` ") as $accounts_momo)
    {
        $money = getMoney_momo($accounts_momo['token']);
        $CMSNT->update("accounts_momo", [
            'money' => $money
        ], " `id` = '".$accounts_momo['id']."' ");
    }