<?php

    function getMoney_momo($token)
    {
        $result = curl_get("https://api.web2m.com/apigetsodu/$token");
        $result = json_decode($result, true);
        if($result['status'] == 200)
        {
            return $result['SoDu'];
        }
        else
        {
            return 0;
        }
    }
    function getName_momo($sdt, $token)
    {
        $result = curl_get("https://api.web2m.com/apigetten/".$sdt."/".$token);
        $result = json_decode($result, true);
        if($result['status'] == 200)
        {
            return $result['name'];
        }
        else
        {
            return $result['msg'];
        }
    }
    function payment_momo($token, $sdtnguoinhan, $password, $money, $noidung)
    {
        $result = curl_get("https://api.web2m.com/TRANSFERAPIMOMO/".$token."/".$sdtnguoinhan."/".$password."/".$money."/".$noidung);
        $result = json_decode($result, true);
        $data = [];
        if($result['status'] == 200)
        {
            $data['msg'] = $result['msg'];
            $data['transId'] = $result['transId'];
            $data['status'] = $result['status'];
        }
        else
        {
            $data['msg'] = $result['msg'];
            $data['status'] = $result['status'];
            $data['transId'] = $result['transId'];
        }
        return $data;
    }
    function KiemTraMaGiaoDich($tranId)
    {
        $tranId = substr($tranId, -1); // TRẢ VỀ SỐ CUỐI CÙNG
        if($tranId == 1 || $tranId == 3 || $tranId == 5 || $tranId == 7 || $tranId == 9)
        {
            return 1;
        }
        else
        {
            return 2;
        }
    }
    function KiemTraNoiDung($comment)
    {
        if($comment == 'c' || $comment == 'C')
        {
            return 2; // CHẴN
        }
        else if($comment == 'l' || $comment == 'L')
        {
            return 1;
        }
        else
        {
            return FALSE; // LẼ
        }
    }
    function processing_momo()
    {
        global $CMSNT;
        $token = $CMSNT->get_row("SELECT * FROM `accounts_momo` WHERE `status` = 'ON' ")['token'];
        $result = curl_get("https://api.web2m.com/historyapimomo1h/$token");
        $result = json_decode($result, true);
        foreach($result['momoMsg']['tranList'] as $data)
        {
            $partnerId      = $data['partnerId'];               // SỐ ĐIỆN THOẠI CHUYỂN
            $comment        = $data['comment'];                 // NỘI DUNG CHUYỂN TIỀN
            $tranId         = $data['tranId'];                  // MÃ GIAO DỊCH
            $partnerName    = $data['partnerName'];             // TÊN CHỦ VÍ
            $amount         = $data['amount'];                  // SỐ TIỀN CHUYỂN
            
            if(KiemTraNoiDung($comment) != FALSE)
            {
                if($CMSNT->num_rows(" SELECT * FROM `momo` WHERE `tranId` = '$tranId' ") == 0) 
                {
                    // TẠO LỊCH SỬ GIAO DỊCH
                    $CMSNT->insert("momo", array(
                        'tranId'        => $tranId,
                        'comment'       => $comment,
                        'gettime'       => gettime(),
                        'time'          => time(),
                        'partnerId'     => $partnerId,
                        'amount'        => $amount,
                        'partnerName'   => $partnerName,
                        'status'        => 'processing'
                    ));
                    if(getSite('min-chan-le') <= $amount && getSite('max-chan-le') >= $amount)
                    {
                        // KIỂM TRA KẾT QUẢ CHỌN
                        if(KiemTraNoiDung($comment) != KiemTraMaGiaoDich($tranId))
                        {
                            // LOSE 
                            $CMSNT->update("momo", [
                                'status'  => 'lose'
                            ], " `tranId` = '$tranId' ");
                        }
                        else
                        {
                            // WIN
                            $CMSNT->update("momo", [
                                'status'  => 'unpaid'
                            ], " `tranId` = '$tranId' ");
                        }  
                    }
                    else
                    {
                        // Refund 
                        $CMSNT->update("momo", [
                            'status'  => 'waiting-refund'
                        ], " `tranId` = '$tranId' ");
                    }
                }
            }     
        }
    }
    function transfer_momo()
    {
        global $CMSNT;
        // CỘNG THƯỞNG
        if($CMSNT->num_rows(" SELECT * FROM `momo` WHERE `status` = 'unpaid' ") != 0)
        {
            foreach($CMSNT->get_list("SELECT * FROM `momo` WHERE `status` = 'unpaid' ") as $unpaid)
            {
                $partnerId      = $unpaid['partnerId'];               // SỐ ĐIỆN THOẠI CHUYỂN
                $comment        = $unpaid['comment'];                 // NỘI DUNG CHUYỂN TIỀN
                $tranId         = $unpaid['tranId'];                  // MÃ GIAO DỊCH
                $partnerName    = $unpaid['partnerName'];             // TÊN CHỦ VÍ
                $amount         = $unpaid['amount'];                  // SỐ TIỀN CHUYỂN

                $thucnhan = $amount - $amount * getSite('chiet-khau-win') / 100;
                $thucnhan = $thucnhan + $amount;
                $noidung_paid = getSite('noidung_chanle').$tranId;
                if($unpaid['status'] != 'unpaid')
                {
                    continue;
                }
                // LẤY DANH SÁCH VÍ MOMO
                foreach($CMSNT->get_list("SELECT * FROM `accounts_momo` WHERE `status` != 'ON' ") as $accounts_momo)
                {
                    if(getMoney_momo($accounts_momo['token']) >= $thucnhan)
                    {
                        if($unpaid['status'] != 'unpaid')
                        {
                            break;
                        }
                        //$result1 = curl_get("https://api.web2m.com/TRANSFERAPIMOMO/".$accounts_momo['token']."/".$partnerId."/".$accounts_momo['password']."/".$thucnhan."/".$noidung_paid);
                        $result1 = payment_momo($accounts_momo['token'], $partnerId, $accounts_momo['password'], $thucnhan, $noidung_paid);
                        if($result1['status'] == 200)
                        {
                            $CMSNT->insert("pay_momo", [
                                'account'    => $accounts_momo['sdt'],
                                'sdt'        => $partnerId,
                                'name'       => '',
                                'money'      => $thucnhan,
                                'noidung'    => $noidung_paid,
                                'transId'    => $result1['transId'],
                                'createdate' => gettime()
                            ]);
                            $CMSNT->update("momo", [
                                'paid'    => $thucnhan,
                                'status'  => 'paid'
                            ], " `tranId` = '$tranId' ");
                            // GHI LOG
                            $file = @fopen('../logs/transfer_momo.txt', 'a');
                            if ($file)
                            {
                                $data = "[Transfer_Momo] Trả thưởng #".$tranId." (".$accounts_momo['sdt']." -> ".$partnerId.") ".gettime().PHP_EOL;
                                fwrite($file, $data);
                                fclose($file);
                            }
                            break;
                        }
                    }
                    else
                    {
                        continue;
                    }
                }
            }
        }
    }
    function refund_momo()
    {
        global $CMSNT;
        // HOÀN TIỀN
        if($CMSNT->num_rows(" SELECT * FROM `momo` WHERE `status` = 'waiting-refund' ") != 0)
        {
            foreach($CMSNT->get_list("SELECT * FROM `momo` WHERE `status` = 'waiting-refund' ") as $refund)
            {
                $partnerId      = $refund['partnerId'];               // SỐ ĐIỆN THOẠI CHUYỂN
                $comment        = $refund['comment'];                 // NỘI DUNG CHUYỂN TIỀN
                $tranId         = $refund['tranId'];                  // MÃ GIAO DỊCH
                $partnerName    = $refund['partnerName'];             // TÊN CHỦ VÍ
                $amount         = $refund['amount'];                  // SỐ TIỀN CHUYỂN

                $noidung_refund = "Hoàn tiền ".$tranId;
                if($refund['status'] != 'waiting-refund')
                {
                    continue;
                }
                // LẤY DANH SÁCH VÍ MOMO
                foreach($CMSNT->get_list("SELECT * FROM `accounts_momo` WHERE `status` != 'ON' ") as $accounts_momo)
                {
                    if(getMoney_momo($accounts_momo['token']) >= $amount)
                    {
                        if($refund['status'] != 'waiting-refund')
                        {
                            break;
                        }
                        //$result1 = curl_get("https://api.web2m.com/TRANSFERAPIMOMO/".$accounts_momo['token']."/".$partnerId."/".$accounts_momo['password']."/".$amount."/".$noidung_refund);
                        $result1 = payment_momo($accounts_momo['token'], $partnerId, $accounts_momo['password'], $amount, $noidung_refund);
                        if($result1['status'] == 200)
                        {
                            $CMSNT->insert("pay_momo", [
                                'account'    => $accounts_momo['sdt'],
                                'sdt'        => $partnerId,
                                'name'       => '',
                                'money'      => $amount,
                                'noidung'    => $noidung_refund,
                                'transId'    => $result1['transId'],
                                'createdate' => gettime()
                            ]);
                            $CMSNT->update("momo", [
                                'status'  => 'refund'
                            ], " `tranId` = '$tranId' ");
                            // GHI LOG
                            $file = @fopen('../logs/refund_momo.txt', 'a');
                            if ($file)
                            {
                                $data = "[Refund_Momo] Hoàn tiền #".$tranId." (".$accounts_momo['sdt']." -> ".$partnerId.") ".gettime().PHP_EOL;
                                fwrite($file, $data);
                                fclose($file);
                            }
                            break;
                        }
                    }
                    else
                    {
                        continue;
                    }
                }
            }
        }
    }

