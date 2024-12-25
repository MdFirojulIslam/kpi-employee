<?php

/**
 * @author Sharif Ahmed
 * @Email winsharif@gmail.com
 * @http://esteemsoftbd.com
 * @copyright 2014
 *
 */

require_once("../class/initialize.php");
require_once("../class/user.php");
// require_once('../class/image-resize.php');

if (isset($_POST['new_teamsetup'])) {


    $tid = isset($_POST["teamleadID"]) && !empty($_POST["teamleadID"]) ? escape($_POST["teamleadID"]) : NULL;
    $mid_array = isset($_POST['memberID']) ? $_POST['memberID'] : []; // Assign members to $mid_array
    $category = isset($_POST['category']) && !empty($_POST['category']) ? escape($_POST['category']) : NULL;
    $status = isset($_POST['status']) && $_POST['status'] == 1 ? 1 : 0; // Simplify status
    $from_date = isset($_POST["from_date"]) && !empty($_POST["from_date"]) ? convert_date(escape($_POST["from_date"])) : NULL;
    $to_date = isset($_POST['to_date']) && !empty($_POST['to_date']) ? convert_date(escape($_POST['to_date'])) : NULL;


    foreach ($mid_array as $memberID) {
        $memberID = escape($memberID);
        if ($tid !== $memberID) {
            $set_teamset = "INSERT INTO hr_kpi_teamset (teamleadID, memberID, category, status, from_date, to_date) 
                        VALUES ('$tid', '$memberID', '$category', '$status', '$from_date', '$to_date')";

            if (!$db->query($set_teamset)) {
                $flash->error("Database error: " . $db->error);
                return;
            }
        }
    }
    $flash->success('New team setup is registered successfully.', 'index.php?page=teamsetup&teamleadID=' . $_POST['teamleadID'] . '&inserting=teamsetup');
}

if (isset($_POST['update_teamsetup'])) {
    if (isset($_GET['teamleadID'])) {
        $teamset_id = $_GET['teamleadID'];

        // Prepare data for updating
        $teamleadID = isset($_POST['teamleadID']) ? $_POST['teamleadID'] : '';
        $memberIDs = isset($_POST['memberID']) ? implode(',', $_POST['memberID']) : '';
        $category = isset($_POST['category']) ? $_POST['category'] : '';
        $status = isset($_POST['status']) ? 1 : 0;
        $from_date = isset($_POST['from_date']) ? convert_date($_POST['from_date']) : '';
        $to_date = isset($_POST['to_date']) ? convert_date($_POST['to_date']) : '';

        // Update query
        $update_teamset = $db->query(" 
            UPDATE `hr_kpi_teamset`
            SET 
                `teamleadID` = '$teamleadID',
                `memberID`   = '$memberIDs',
                `category`   = '$category',
                `status`     = '$status',
                `from_date`  = '$from_date',
                `to_date`    = '$to_date'
            WHERE `teamleadID` = '$teamset_id'
        ");

        // Check if update was successful
        if ($update_teamset) {
            $flash->success('Teamsetup updated successfully.', 'index.php?page=teamsetup&teamsetupID=' . $teamset_id . '&updating=teamsetup');
        } else {
            $flash->error('Failed to update teamsetup. Please try again. Error: ' . $db->errorInfo()[2]);
        }
    }
}



function fetchTeamMember($teamleadID = null)
{
    global $db;
    $users = $db->result_all("SELECT  t1.id AS kpi_id, t1.teamleadID, t1.memberID, t1.category, t1.status, t1.from_date, t1.to_date,
                 t2.fname AS teamlead_fname, t2.lname AS teamlead_lname,  
                 t3.fname AS member_fname, t3.lname AS member_lname
                FROM hr_kpi_teamset AS t1                
                LEFT JOIN user AS t2 ON t1.teamleadID = t2.id
                LEFT JOIN user AS t3 ON t1.memberID = t3.id
                WHERE t1.teamleadID = $teamleadID");

    $teamLeaderName = null;
    $memberDetails = [];
    $fromDate = null;
    $toDate = null;

    if (!empty($users)) {
        $teamLeaderName = $users[0]->teamlead_fname . ' ' . $users[0]->teamlead_lname;
        $fromDate = $users[0]->from_date; // Capture from_date
        $toDate = $users[0]->to_date;     // Capture to_date

        foreach ($users as $user) {
            $memberDetails[] = [
                'member_name' => $user->member_fname . ' ' . $user->member_lname,
                'category' => $user->category,
                'status' => $user->status,
                'from_date' => $user->from_date,
                'to_date' => $user->to_date
            ];
        }
    }

    return [
        'team_leader_name' => $teamLeaderName,
        'members' => $memberDetails,
        'memberIDs' => array_column($users, 'memberID'), // Extract all member IDs
        'from_date' => $fromDate,
        'to_date' => $toDate
    ];
}



$edit_data = null;
if (isset($_GET['teamleadID'])) {
    $edit_data = fetchTeamMember($_GET['teamleadID']);
}



// function fetchKpiData($teamID = null)
// {
//     global $database;
//     $sql = "SELECT  t1.id AS kpi_id, t1.teamleadID, t1.memberID, t1.category, t1.status,t1.from_date, t1.to_date,
//                 t2.fname AS teamlead_fname, t2.lname AS teamlead_lname,  
//                 t3.fname AS member_fname, t3.lname AS member_lname
//                 FROM hr_kpi_teamset AS t1
//                 LEFT JOIN user AS t2 ON t1.teamleadID = t2.id
//                 LEFT JOIN user AS t3 ON t1.memberID = t3.id
//                 WHERE t1.id = $teamID";
//     $stmt = $database->prepare($sql);
//     $stmt->execute();
//     $results = $stmt->get_result();
//     return ($teamID !== null) ? $results->fetch_assoc() : $results->fetch_all(MYSQLI_ASSOC);
// }

// $edit_data = null;
// if (isset($_GET['teamID'])) {
//     $edit_data = fetchKpiData($_GET['teamID']);
// }

?>

<!-- <style>
	/*Remove Placeholder in Print Mode*/
	@media print {
		::-webkit-input-placeholder {
			/* WebKit browsers */
			color: transparent !important;
		}
		:-moz-placeholder {
			/* Mozilla Firefox 4 to 18 */
			color: transparent !important;
		}
		::-moz-placeholder {
			/* Mozilla Firefox 19+ */
			color: transparent !important;
		}
		:-ms-input-placeholder {
			/* Internet Explorer 10+ */
			color: transparent !important;
		}
	}
</style> -->

<style type="text/css">
    @media (max-width: 360px) {
        .panel-info .form-group .radio-group {
            font-size: 12px;
        }
    }

    @media (min-width: 1024px) and (max-width: 1100px) {
        .panel-info .form-group .radio-group {
            font-size: 10px;
        }
    }

    /*Custom Radio Button Group Ends*/

    .image-wrapper {
        background: #efefef;
        text-align: center;
        border-radius: 10px;
        padding: 5px 0 12px;
    }

    /*Patient Photo*/
    /*Photo Wrapper*/
    .patient-admission .fileinput-new.img-thumbnail,
    .student-update-page .fileinput-new.img-thumbnail {
        width: 120px;
        height: 120px;
        line-height: unset !important;
    }

    .patient-admission .fileinput-preview.fileinput-exists.img-thumbnail,
    .student-update-page .fileinput-preview.fileinput-exists.img-thumbnail {
        width: 120px;
        height: 120px;
        line-height: unset !important;
    }

    /*Photo Wrapper*/
    /*Photo*/
    .patient-admission .fileinput-preview.fileinput-exists.img-thumbnail>img,
    .student-update-page .fileinput-preview.fileinput-exists.img-thumbnail>img {
        width: 110px;
        height: 110px;
    }

    .patient-admission .fileinput-new.img-thumbnail>img,
    .student-update-page .fileinput-new.img-thumbnail>img {
        width: 110px;
        height: 110px;
    }

    /*Photo*/
    .patient-admission .fileinput-new span.fileinput-new,
    .student-update-page .fileinput-new span.fileinput-new {
        padding: 5px 7px;
    }
</style>

<div id="page-wrapper">
    <div class="row print_div" id='print-div1'>
        <div class="col-lg-12 kpi-teamsetup">
            <?php echo $flash->display(); ?>
            <form action="" role="form" method="POST" enctype="multipart/form-data">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <i class="fas fa-user-plus"></i>
                        <?php if (isset($_GET['teamID'])) { ?>
                            KPI Team Setup Update<?php } else { ?>KPI Team Setup <?php } ?>
                    </div>
                    <div class="panel-body">
                        <div class="row">



                            <div class="col-lg-4 col-md-4 left-side">
                                <div class="form-group">
                                    <label>Team Leader</label>
                                    <input type="hidden" id="searchType" value="employee" />
                                    <select name="teamleadID" id='contact_ajax_search' class="form-control">
                                        <option value="">Team Leader</option>
                                        <?php
                                        $user_ids = HRM::fetch_user_id();
                                        $selected_teamleadID = isset($_GET['teamleadID']) ? $_GET['teamleadID'] : '';

                                        foreach ($user_ids as $user) {
                                            $selected = ($user['id'] == $selected_teamleadID) ? 'selected' : '';
                                            echo "<option value='" . $user['id'] . "' $selected>" . $user['full_name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 left-side">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="text" required class="form-control" name="from_date"
                                        value="<?php echo isset($edit_data['from_date']) ? return_date($edit_data['from_date']) : ''; ?>"
                                        id="date1" placeholder="From Date" />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 left-side">
                                <div class="form-group">
                                    <label>To Date *</label>
                                    <input type="text" required class="form-control" name="to_date"
                                        value="<?php echo isset($edit_data['to_date']) ? return_date($edit_data['to_date']) : ''; ?>"
                                        id="date2" placeholder="To Date" />
                                </div>
                            </div>

                        </div>



                        <div class="row">
                            <div class="col-lg-4 col-md-4 left-side">
                                <div class="form-group">
                                    <label for="support">Member</label>
                                    <select class="form-control select2" id="support" name="memberID[]" style="width: 100%;" tabindex="-1" aria-hidden="true" multiple>
                                        <option value="">None</option>
                                        <?php
                                        $user_ids = HRM::fetch_user_id();
                                        $selected_members = isset($edit_data['memberIDs']) ? $edit_data['memberIDs'] : [];

                                        foreach ($user_ids as $user) {
                                            $selected = in_array($user['id'], $selected_members) ? 'selected' : '';
                                            echo "<option value='" . $user['id'] . "' $selected>" . $user['full_name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>


                            <div class="col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label>Category</label>
                                    <input type="hidden" id="searchType" value="category" />
                                    <select class="form-control" id="category" name="category" style="width: 100%;" tabindex="-1" aria-hidden="true">
                                        <option value="1" <?php echo (isset($edit_data['members'][0]['category']) && $edit_data['members'][0]['category'] == '1') ? 'selected' : ''; ?>>Main</option>
                                        <option value="0" <?php echo (isset($edit_data['members'][0]['category']) && $edit_data['members'][0]['category'] == '0') ? 'selected' : ''; ?>>Optional</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2 col-md-2">
                                <div class="form-group pull-right">
                                    <label class="block">Status</label>
                                    <label class="switch-wrapper">
                                        <input type="checkbox" id="togBtn" name="status" value="1"
                                            <?php echo (isset($edit_data['members'][0]['status']) && $edit_data['members'][0]['status'] == '1') ? 'checked' : ''; ?> />
                                        <div class="slider round">
                                            <span class="on">Active</span><span class="off">Inactive</span>
                                        </div>
                                    </label>
                                </div>
                            </div>


                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group" style="margin-top: 22px;">
                                    <div class="pull-right">
                                        <?php if (isset($edit_data)): ?>
                                            <div class="pull-right">
                                                <input type="submit" name="update_teamsetup" class="btn-new avoid" value="Update">
                                            </div>
                                        <?php else: ?>
                                            <div class="pull-right">
                                                <input type="submit" name="new_teamsetup" class="btn-new avoid" value="SAVE">
                                                <button type="reset" class="btn-reset avoid">Reset</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Add other columns and inputs as in the original code -->
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
    <?php if ($flash->hasMessages()): ?>
        <div class="flash-messages">
            <?php echo $flash->display(); ?>
        </div>
    <?php endif; ?>
</div>



<style type="text/css">
    .employeeContactBox {
        background: #efefef;
        padding: 15px;
        border-radius: 20px;
        margin: 1em 0;
    }
</style>

<script type="text/javascript">
    function climit(element) {
        var max_chars = 11;

        if (element.value.length > max_chars) {
            element.value = element.value.substr(0, max_chars);
        }
        // Employee Contact Number
        if (element.value.length == max_chars) {
            $("input.form-control.cnumber").css({
                "border-color": "#00cd00!important",
                "box-shadow": "inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(0,205,0,1)"
            });
        }
        if (element.value.length < max_chars) {
            $("input.form-control.cnumber").css({
                "border-color": "#FF0000!important",
                "box-shadow": "inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(255,0,0,1)"
            });
        }
    }
</script>
<?php
$footer_link = '
<script src="' . SITE_URL . 'js/chosen.jquery.min.js" type="text/javascript"></script>
<!-- Photo Add/Remove -->
<link href="' . SITE_URL . 'includes/jasny-bootstrap/css/jasny-bootstrap.min.css" rel="stylesheet" media="screen">
<script src="' . SITE_URL . 'includes/jasny-bootstrap/js/jasny-bootstrap.min.js" type="text/javascript"></script>

<!-- Select 2 -->
<link rel="stylesheet" type="text/css" href="' . SITE_URL . 'css/select2.min.css">
<script src="' . SITE_URL . 'js/select2.min.js" type="text/javascript"></script>

';
?>