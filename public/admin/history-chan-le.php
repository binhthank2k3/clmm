<?php
    require_once("../../config/config.php");
    require_once("../../config/function.php");
    $title = 'LỊCH SỬ CHƠI CHẴN LẺ | '.getSite('title');
    require_once("Header.php");
    require_once("Sidebar.php");
    CheckLogin();
    CheckAdmin();
?>

<?php
if(isset($_POST['btnTimKiem']))
{
    $daterangepicker    = check_string($_POST['daterangepicker']);
    $daterangepicker    = explode(' - ', $daterangepicker);
    $listMomo           = $CMSNT->get_list(" SELECT * FROM `momo` WHERE `gettime` >= '".$daterangepicker[0]."' AND `gettime` <= '".$daterangepicker[1]."' ORDER BY id DESC ");
    $total_phien        = $CMSNT->num_rows("SELECT * FROM `momo` WHERE `gettime` >= '".$daterangepicker[0]."' AND `gettime` <= '".$daterangepicker[1]."' ");
    $total_cuoc         = $CMSNT->get_row("SELECT SUM(`amount`) FROM `momo` WHERE `status` != 'refund' AND `gettime` >= '".$daterangepicker[0]."' AND `gettime` <= '".$daterangepicker[1]."' ")['SUM(`amount`)'];
    $total_trathuong    = $CMSNT->get_row("SELECT SUM(`paid`) FROM `momo` WHERE `gettime` >= '".$daterangepicker[0]."' AND `gettime` <= '".$daterangepicker[1]."' ")['SUM(`paid`)'];
}  
else
{

    $total_phien         = $CMSNT->num_rows("SELECT * FROM `momo` ");
    $total_cuoc          = $CMSNT->get_row("SELECT SUM(`amount`) FROM `momo` WHERE `status` != 'refund' ")['SUM(`amount`)'];
    $total_trathuong     = $CMSNT->get_row("SELECT SUM(`paid`) FROM `momo` ")['SUM(`paid`)'];
    $listMomo            = $CMSNT->get_list(" SELECT * FROM `momo` ORDER BY id DESC ");
}
?>


<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Lịch sử chơi chẵn lẻ</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div id="thongbao"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">LỊCH SỬ GIAO DỊCH CHẴN LẺ</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="far fa-calendar-alt"></i>
                                                </span>
                                            </div>
                                            <input type="text" name="daterangepicker" class="form-control float-right"
                                                id="reservationtime">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <button type="submit" name="btnTimKiem" class="btn btn-primary">
                                        <span>TÌM KIẾM</span></button>
                                </div>
                            </div>
                        </form>
                        <ul>
                            <li>Tổng phiên: <b><?=format_cash($total_phien);?></b></li>
                            <li>Tổng cược: <b style="color: green;"><?=format_cash($total_cuoc);?>đ</b></li>
                            <li>Tổng trả tưởng: <b style="color: blue;"><?=format_cash($total_trathuong);?>đ</b></li>
                            <li>Lợi nhuận: <b style="color: red;"><?=format_cash($total_cuoc - $total_trathuong);?>đ</b>
                            </li>
                        </ul>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>MÃ GD</th>
                                        <th>SDT</th>
                                        <th>NAME</th>
                                        <th>AMOUNT</th>
                                        <th>COMMENT</th>
                                        <th>TIME</th>
                                        <th>STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 0; foreach($listMomo as $row){ ?>
                                    <tr>
                                        <td><?=$i++;?></td>
                                        <td><b><?=$row['tranId'];?></b></td>
                                        <td><?=$row['partnerId'];?></td>
                                        <td><?=$row['partnerName'];?></td>
                                        <td><?=$row['amount'];?></td>
                                        <td><?=$row['comment'];?></td>
                                        <td><span class="badge badge-dark"><?=$row['gettime'];?></span></td>
                                        <td><?=dispay_status($row['status']);?></td>
                                    </tr>
                                    <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>



<script>
$(function() {
    $('#reservationtime').daterangepicker({
        timePicker: true,
        timePickerIncrement: 30,
        locale: {
            format: 'YYYY/MM/DD/ hh:mm:ss'
        }
    })
    $("#datatable").DataTable({
        "responsive": true,
        "autoWidth": false,
    });
});
</script>

<?php 
    require_once("Footer.php");
?>