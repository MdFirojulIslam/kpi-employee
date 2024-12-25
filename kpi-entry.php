<?php

/**
 * @author Sharif Ahmed
 * @Email winsharif@gmail.com
 * @http://esteemsoftbd.com
 * @copyright 2014
 *
 */

require_once("pagination.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert-kpi'])) {
    $teamleadID = isset($_POST["teamleadID"]) && !empty($_POST["teamleadID"]) ? escape($_POST["teamleadID"]) : NULL;
    $mark_date = isset($_POST["mark_date"]) && !empty($_POST["mark_date"]) ? convert_date(escape($_POST["mark_date"])) : NULL;
    if (!empty($_POST['checkbox'])) {
        $insertQueries = [];
        foreach ($_POST['checkbox'] as $checkedKey) {
            $keyParts = explode('_', $checkedKey);
            $type = $keyParts[0];
            $index = $keyParts[1];
            $teamID = isset($_POST["teamID_{$index}"]) ? escape($_POST["teamID_{$index}"]) : NULL;
            $memberID = isset($_POST["memberID_{$index}"]) ? escape($_POST["memberID_{$index}"]) : NULL;
            $radioKey = "{$type}_{$index}";
            $remarksKey = "{$type}_remarks{$index}";
            if (isset($_POST[$radioKey])) {
                $kpiValue = escape($_POST[$radioKey]);
                $remarks = isset($_POST[$remarksKey]) ? escape($_POST[$remarksKey]) : NULL;

                if ($memberID && $teamID && $kpiValue) {
                    $insertQueries[] = "('$teamID', '$memberID', '$kpiValue', '$remarks', '$mark_date', '$teamleadID')";
                }
            }
        }
        if (!empty($insertQueries)) {
            $insertQuery = "INSERT INTO hr_kpi_markset (teamID, memberID, mark, note, mark_date, created_by)
                            VALUES " . implode(", ", $insertQueries);
            $insert = $db->query($insertQuery);
            if ($insert) {
                $flash->success('KPI entries successfully saved.', 'index.php?page=kpi-entry&teamleadID=' . $teamleadID . '&inserting=kpi-entry');
            } else {
                $flash->error('Failed to insert. Please try again.');
            }
        } else {
            $flash->error('No valid checkboxes selected.');
        }
    } else {
        $flash->error('No checkboxes selected.');
    }
}






if (isset($_GET["search_entry"])) {
    $teamLeadID = isset($_GET["teamleadID"]) && !empty($_GET["teamleadID"]) ? escape($_GET["teamleadID"]) : NULL;
    $mark_date = isset($_GET["mark_date"]) && !empty($_GET["mark_date"]) ? convert_date(escape($_GET["mark_date"])) : NULL;

    $per_page = isset($_GET['per_page']) ? escape($_GET["per_page"]) : 25;
    $page = (int)(!isset($_GET["pages"]) ? 1 : $_GET["pages"]);
    if ($page <= 0) $page = 1;
    $startpoint = ($page * $per_page) - $per_page;

    $statement = "hr_kpi_teamset AS t1
                  LEFT JOIN user AS t2 ON t1.teamleadID = t2.id
				  WHERE t1.id<>'0'";

    empty($teamLeadID) ? NULL : $statement .= " AND t1.teamleadID = '$teamLeadID'";
    // empty($mark_date) ? NULL :  $statement .= " AND t1.mark_date != '$mark_date'";

    $users = $db->result_all("SELECT t1.id AS kpi_teamID, t1.teamleadID, t1.memberID, t1.category,
                                    CONCAT(t2.fname, ' ', t2.lname) AS teamLeaderName
                             		FROM {$statement} LIMIT {$startpoint}, {$per_page}");


    $user_info = []; {
        foreach ($users as $data) {
            $kpi_teamID = $data->kpi_teamID;
            $teamleadID = $data->teamleadID;
            $teamLeaderName = $data->teamLeaderName;
            if (!isset($user_info[$teamleadID])) {
                $user_info[$teamleadID] = [
                    'teamLeaderName' => $teamLeaderName,
                    'main_members' => [],
                    'optional_members' => []
                ];
            }
            if ($data->category == 1) {
                $user_info[$teamleadID]['main_members'][] = [
                    'mainMemberID' => $data->memberID,
                    'kpi_teamID' => $kpi_teamID
                ];
            } elseif ($data->category == 0) {
                $user_info[$teamleadID]['optional_members'][] = [
                    'optionalMemberID' => $data->memberID,
                    'kpi_teamID' => $kpi_teamID
                ];
            }
        }
    }
    $count_query = "SELECT COUNT(t1.teamleadID) AS num FROM {$statement}";
    $total_member = $db->result_one($count_query);
    $total_member = $total_member->num ?? 0;
}

?>

<div id="page-wrapper">
    <div class="row print_div" id="print-div1">
        <div class="col-lg-12">
            <?php echo $flash->display(); ?>
            <div class="panel panel-info">
                <div class="panel-heading avoid">
                    <?php if (isset($edit_data['memberID'])) { ?>
                        KPI Update<?php } else { ?>KPI Entry <?php } ?>
                    <?php if (isset($total_member)) { ?>
                        <div class="pull-right">
                            <button class="btn-white btn-print avoid" id="print" type="button">
                                <i class="fa fa-print"></i> Print
                            </button>
                            <button class="btn-white btn-export dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i> Export Data</button>
                            <ul class="dropdown-menu arch-main" role="menu">
                                <li><a href="#" onclick="$('#table').tableExport({type: 'excel',fileName: 'Employee_List_'+ToDay(),ignoreColumn:[8,9]});"> <img src='<?php echo SITE_URL; ?>images/xls.png' alt="PNG" style="width:24px" /> Excel</a></li>
                                <li><a href="#" onclick="$('#table').tableExport({type: 'pdf',jspdf: {orientation: 'bestfit',format: 'a4',margins: {right: 10, left: 10, top: 30, bottom: 10},autotable: {tableWidth: 'auto'}},fileName: 'Employee_List_'+ToDay(),ignoreColumn:[8,9]});"> <img src="<?php echo SITE_URL; ?>images/pdf.png" width="24px" /> PDF(P)</a></li>
                                <li><a href="#" onclick="$('#table').tableExport({type: 'pdf',jspdf: {orientation: 'l',margins: {right: 10, left: 10, top: 30, bottom: 10},autotable: {tableWidth: 'auto'}},fileName: 'Employee_List_'+ToDay(),ignoreColumn:[8,9]});"> <img src="<?php echo SITE_URL; ?>images/pdf.png" width="24px" /> PDF(L)</a></li>
                                <li><a href="#" onclick="$('#table').tableExport({type: 'doc',fileName: 'Employee_List_'+ToDay(),ignoreColumn:[8,9]});"> <img src='<?php echo SITE_URL; ?>images/word.png' alt="PNG" style="width:24px" /> Word</a></li>
                                <li><a href="#" onclick="$('#table').tableExport({type: 'csv',fileName: 'Employee_List_'+ToDay(),ignoreColumn:[8,9]});"> <img src='<?php echo SITE_URL; ?>images/csv.png' alt="PNG" style="width:24px" /> CVC</a></li>
                                <li><a href="#" onclick="$('#table').tableExport({type: 'png',fileName: 'Employee_List_'+ToDay(),ignoreColumn:[8,9]});"> <img src='<?php echo SITE_URL; ?>images/png.png' alt="PNG" style="width:24px" /> PNG</a></li>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
                <!-- /.panel-heading -->
                <div class="panel-body">
                    <form action="" role="form" method="GET">
                        <input type="hidden" name="page" value="kpi-entry" />
                        <div class="row avoid">
                            <div class="searchFieldWrapper clearfix">

                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Team Leader</label>
                                        <input type="hidden" id="searchType" value="employee" />
                                        <select name="teamleadID" id="contact_ajax_search" class="form-control" required>
                                            <option value="">Team Leader</option>
                                            <?php
                                            $users = HRM::fetch_user_id();
                                            $selectedTeamleadID = isset($_GET['teamleadID']) ? $_GET['teamleadID'] : '';

                                            foreach ($users as $user) {
                                                $isSelected = ($user['id'] == $selectedTeamleadID) ? 'selected' : '';
                                                echo "<option value='" . $user['id'] . "' $isSelected>" . $user['full_name'] . "</option>";
                                            }
                                            ?>

                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Member</label>
                                        <input type="hidden" id="searchType2" value="employee" />
                                        <select name="memberID" id="contact_ajax_search2" class="form-control">
                                            <option value="">Member</option>
                                            <?php
                                            $users = HRM::fetch_user_id();
                                            foreach ($users as $user) {
                                                echo "<option value='" . $user['id'] . "'>" . $user['full_name'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Date *</label>
                                        <input type="text" class="form-control" name="mark_date" value="<?php echo current_date(); ?>" id="date1" placeholder="Date" required />
                                    </div>
                                </div>

                                <div class="col-lg-1 col-sm-6">
                                    <div class="form-group">
                                        <div class="pull-right" style="margin-top: 22px;">
                                            <input type="submit" name="search_entry" class="btn-new avoid" value="Search">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>


                    <!-- Custom Search Field For Print View Starts -->

                    <div class="quickInfoSectionWrapper">
                        <form action="" role="form" method="POST">
                            <?php
                            if (isset($total_member ) > 0) {
                            ?>
                                <div class="row">
                                    <div class="searchQuickInfoSectionWrapper">
                                        <div class="col-md-4 col-lg-4">
                                            <div class="searchQuickInfoSection quickInfoDataTable">
                                                <div class="sectionLeft">
                                                    <i class="fas fa-users"></i> Total Member
                                                </div>
                                                <div class="sectionRight">
                                                    <?php echo $total_member; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped employee-list-table">
                                        <thead>
                                            <tr>
                                                <th class="text-left">SN</th>
                                                <th class="text-left">Member</th>
                                                <th class="text-middle">KPI Entry</th>
                                                <th class="text-middle" width="15%">Remarks</th>
                                                <th class="text-right">
                                                    <div class="checkbox-wrapper">
                                                        <label>
                                                            <input id="check_all" type="checkbox">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                    </div>
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($user_info)) {
                                                $sn = 1;
                                                foreach ($user_info as $teamleadID => $row) { ?>
                                                    <input type="hidden" name="mark_date" value="<?php echo current_time(); ?>">
                                                    <input type="hidden" name="teamleadID" value="<?php echo $teamleadID; ?>">
                                                    <tr>
                                                        <td colspan="5" class="text-left" style="font-weight:bold;"><?php echo "{$row['teamLeaderName']}"; ?></td>
                                                    </tr>

                                                    <?php if (!empty($row['main_members'])) { ?>
                                                        <tr>
                                                            <td colspan="5" class="text-left">Main members</td>
                                                        </tr>

                                                        <?php foreach ($row['main_members'] as $index => $member) {
                                                            $mainMemberId = $member['mainMemberID'];
                                                            $teamID = $member['kpi_teamID'];
                                                        ?>
                                                            <tr>
                                                                <td class="text-left"> <?php echo $sn++; ?></td>
                                                                <td class="text-left"> <?php $users = HRM::fetch_user_id();
                                                                                        $mainMemberName = '';
                                                                                        foreach ($users as $user) {
                                                                                            if ($user['id'] == $mainMemberId) {
                                                                                                $mainMemberName = $user['full_name'];
                                                                                                break;
                                                                                            }
                                                                                        }
                                                                                        echo $mainMemberName; ?></td>


                                                                <td class="text-middle" style="display: flex; justify-content: space-around; align-items: center;">
                                                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                                                        <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                                                                            <label style="margin-bottom: 5px;"><?php echo $i; ?></label>
                                                                            <input type="radio" name="active_<?php echo $index; ?>" value="<?php echo $i; ?>" />
                                                                        </div>
                                                                    <?php } ?>
                                                                </td>
                                                                <td class="text-right">
                                                                    <input type="hidden" name="teamID_<?php echo $index; ?>" value="<?php echo $teamID; ?>" />
                                                                    <input type="hidden" name="memberID_<?php echo $index; ?>" value="<?php echo  $mainMemberId; ?>" />
                                                                    <input type="text" class="form-control" value="" id="" name="active_remarks<?php echo $index; ?>">
                                                                </td>
                                                                <td class="text-right">
                                                                    <div class="checkbox-wrapper">

                                                                        <label>
                                                                            <input type="checkbox" class="case" name="checkbox[]" value="active_<?php echo $index; ?>">
                                                                            <span class="checkmark"></span>
                                                                        </label>
                                                                    </div>
                                                                </td>

                                                            </tr>
                                                    <?php }
                                                    } ?>
                                                    <?php if (!empty($row['optional_members'])) { ?>
                                                        <tr>
                                                            <td colspan="5" class="text-left">Optional Members</td>
                                                        </tr>

                                                        <?php foreach ($row['optional_members'] as $index => $member) {
                                                            $optionalMemberID = $member['optionalMemberID'];
                                                            $teamID = $member['kpi_teamID'];
                                                        ?>
                                                            <tr>
                                                                <td class="text-left"> <?php echo $sn++; ?></td>
                                                                <td class="text-left"> <?php $users = HRM::fetch_user_id();
                                                                                        $optionalMemberName = '';
                                                                                        foreach ($users as $user) {
                                                                                            if ($user['id'] == $optionalMemberID) {
                                                                                                $optionalMemberName = $user['full_name'];
                                                                                                break;
                                                                                            }
                                                                                        }
                                                                                        echo $optionalMemberName; ?></td>
                                                                <td class="text-left" style="display: flex; justify-content: space-around; align-items: center;">
                                                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                                                        <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                                                                            <label style="margin-bottom: 5px;"><?php echo $i; ?></label>
                                                                            <input type="radio" name="optional_<?php echo $index; ?>" value="<?php echo $i; ?>" <?php echo ($i === 1) ? 'checked' : ''; ?> />
                                                                        </div>
                                                                    <?php } ?>
                                                                </td>
                                                                <td class="text-right">
                                                                    <input type="hidden" name="teamID_<?php echo $index; ?>" value="<?php echo $teamID; ?>" />
                                                                    <input type="hidden" name="memberID_<?php echo $index; ?>" value="<?php echo  $optionalMemberID; ?>" />
                                                                    <input type="text" class="form-control" value="" id="" name="optional_remarks<?php echo $index; ?>">
                                                                </td>
                                                                <td class="text-right">
                                                                    <div class="checkbox-wrapper">
                                                                        <label>
                                                                            <input type="checkbox" class="case" name="checkbox[]" value="optional_<?php echo $index; ?>">
                                                                            <span class="checkmark"></span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                    <?php }
                                                    } ?>
                                            <?php }
                                            } ?>
                                        </tbody>
                                    </table>
                                    <div class="pull-right">
                                        <input type="submit" name="insert-kpi" class="btn-new avoid" value="Submit">
                                    </div>
                                </div>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /.table-responsive -->
        </div>
        <!-- /.panel-body -->



    </div>
</div>
<!-- /.panel -->
</div>
</div>
<!-- /.col-lg-12 -->
</div>
</div>
</div>


<style type="text/css">
    @media (min-width: 992px) {
        #page-wrapper .panel .panel-body .table-responsive {
            overflow-x: inherit !important;
        }
    }

    @media (min-width: 1366px) and (max-width: 1600px) {

        .checkbox-inline+.checkbox-inline,
        .radio-inline+.radio-inline {
            margin-left: 0;
        }
    }

    @media (min-width: 1200px) and (max-width: 1600px) {
        .customWidth {
            width: 13%;
        }
    }

    /*Student Photo*/
    td.student-pic-wrapper>img {
        height: 50px;
        width: 50px;
        border-radius: 50%;
        border: 2px solid #35D27C;
        padding: 1px
    }

    /*Custom Select 2 Item Font Size*/
    .form-group.customColumn li.select2-selection__choice {
        font-size: 11px;
        margin: 2px;
    }

    .myFont .select2-container {
        font-size: 4px;
    }

    @media print {
        .searchQuickInfoSectionWrapper>div {
            width: 100%;
        }

        @page {
            size: a4 portrait;
            margin: 15pt;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkAll = document.getElementById('check_all');
        const checkboxes = document.querySelectorAll('.case');

        checkAll.addEventListener('change', () => {
            checkboxes.forEach(checkbox => {
                checkbox.checked = checkAll.checked;
            });
        });
    });

    $(".select2").select2();
    $("#e2").select2({
        dropdownCssClass: "myFont"
    });
</script>

<?php
$footer_link = '
<link rel="stylesheet" type="text/css" href="' . SITE_URL . 'includes/datatables/css/jquery.dataTables2.css">
<script type="text/javascript" language="javascript" src="' . SITE_URL . 'includes/datatables/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="' . SITE_URL . 'css/select2.min.css">
<script src="' . SITE_URL . 'js/select2.min.js" type="text/javascript"></script>
<script src="' . SITE_URL . 'js/chosen.jquery.min.js" type="text/javascript"></script>
<script src="' . SITE_URL . 'includes/exportTable.js" type="text/javascript"></script>
<script>
$(".select2").select2({
  tags: true
});

$(".select2").on("select2:select", function (evt) {
  var element = evt.params.data.element;
  var $element = $(element);

  $element.detach();
  $(this).append($element);
  $(this).trigger("change");
});
</script>
';
?>