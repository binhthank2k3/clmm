<?php
    require_once("../../config/config.php");
    require_once("../../config/function.php");
    require_once("../../config/chanle.php");
    $title = 'CHUYỂN TIỀN MOMO | '.getSite('title');
    require_once("../../public/admin/Header.php");
    require_once("../../public/admin/Sidebar.php");
    CheckLogin();
    CheckAdmin();
?>
<?php
if(isset($_GET['id']) && $getUser['level'] == 'admin')
{
    $id = check_string($_GET['id']);
    $row = $CMSNT->get_row("SELECT * FROM `accounts_momo` WHERE `id` = '$id' ");
    if(!$row)
    {
        admin_msg_error("Dữ liệu này không tôn tại trong hệ thống", BASE_URL('public/admin/momo.php'), 2000);
    }
}
if(isset($_POST['submit']) && $getUser['level'] == 'admin')
{
    $sdt = check_string($_POST['sdt']);
    $noidung = check_string($_POST['noidung']);
    $money = check_string($_POST['money']);

    if(empty($sdt))
    {
        admin_msg_error('Vui lòng nhập số điện thoại', '', 2000);
    }
    if(empty($money))
    {
        admin_msg_error('Vui lòng nhập số tiền cần chuyển', '', 2000);
    }
    $data = payment_momo($row['token'], $sdt, $row['password'], $money, $noidung);
    if($data['status'] != 200)
    {
        admin_msg_error($data['msg'], '', 2000);
    }
    else
    {
        $CMSNT->insert("pay_momo", [
            'account'    => $row['sdt'],
            'sdt'        => $sdt,
            'name'       => '',
            'money'      => $money,
            'noidung'    => $noidung,
            'transId'    => $data['transId'],
            'createdate' => gettime()
        ]);
        admin_msg_success('Chuyển tiền thành công !', '', 2000);
    }
}
?>



<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chuyển tiền ví MOMO</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">CHUYỂN TIỀN ĐẾN VÍ MOMO KHÁC</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Số điện thoại chuyển tiền</label>
                                <div class="col-sm-9">
                                    <div class="form-line">
                                        <input type="text" value="<?=$row['sdt'];?>" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nhập số điện thoại nhận tiền</label>
                                <div class="col-sm-9">
                                    <div class="form-line">
                                        <input type="text" name="sdt" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nhập số tiền cần chuyển</label>
                                <div class="col-sm-9">
                                    <div class="form-line">
                                        <input type="number" name="money"  class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nội dung nhận tiền</label>
                                <div class="col-sm-9">
                                    <div class="form-line">
                                        <input type="text" name="noidung" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary btn-block">
                                <span>CHUYỂN TIỀN NGAY</span></button>
                                <a type="button" href="<?=BASE_URL('public/admin/momo.php');?>"
                                class="btn btn-danger btn-block waves-effect">
                                <span>TRỞ LẠI</span>
                            </a>    
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">NHẬT KÝ CHUYỂN TIỀN</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="thongbao"></div>
                        <div class="table-responsive">
                            <table id="datatable1" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>ACCOUNT</th>
                                        <th>SDT NHẬN</th>
                                        <th>SỐ TIỀN</th>
                                        <th>NỘI DUNG</th>
                                        <th>THỜI GIAN</th>
                                        <th>MÃ GIAO DỊCH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0; foreach($CMSNT->get_list(" SELECT * FROM `pay_momo` ORDER BY id DESC ") as $row){ ?>
                                    <tr>
                                        <td><?=$i++;?></td>
                                        <td><?=$row['account'];?></td>
                                        <td><?=$row['sdt'];?></td>
                                        <td><?=format_cash($row['money']);?>đ</td>
                                        <td><?=$row['noidung'];?></td>
                                        <td><span class="badge badge-dark"><?=$row['createdate'];?></span></td>
                                        <td><?=$row['transId'];?></td>
                                    </tr>
                                    <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>


<script>
$(function() {
    $("#datatable").DataTable({
        "responsive": true,
        "autoWidth": false,
    });
    $("#datatable1").DataTable({
        "responsive": true,
        "autoWidth": false,
    });
    $("#datatable2").DataTable({
        "responsive": true,
        "autoWidth": false,
    });
});
</script>

<?php 
    require_once("../../public/admin/Footer.php");
?>