<?php

/**
 * @author Sharif Ahmed
 * @Email winsharif@gmail.com
 * @http://esteemsoftbd.com
 * @copyright 2014
 *
 */

require_once("pagination.php");

if (isset($_GET["search_teamsetup"])) {
	$tid = isset($_GET["teamleadID"]) && !empty($_GET["teamleadID"]) ? escape($_GET["teamleadID"]) : NULL;
	$mid = isset($_GET["memberID"]) && !empty($_GET["memberID"]) ? escape($_GET["memberID"]) : NULL;
	//$from_date = isset($_GET["from_date"]) && !empty($_GET["from_date"]) ? convert_date(escape($_GET["from_date"])) : NULL;
	//$to_date = isset($_GET["to_date"]) && !empty($_GET["to_date"]) ? convert_date(escape($_GET["to_date"])) : NULL;
	$category = isset($_GET["category"]) && $_GET["category"] !== "" ? escape($_GET["category"]) : NULL;
	$status = isset($_GET["status"]) && $_GET["status"] !== "" ? escape($_GET["status"]) : NULL;
	
	$per_page = isset($_GET['per_page']) ? escape($_GET["per_page"]) : 25;
	$page = (int)(!isset($_GET["pages"]) ? 1 : $_GET["pages"]);
	if ($page <= 0) $page = 1;
	$startpoint = ($page * $per_page) - $per_page;

	$statement = "hr_kpi_teamset AS t1
                  LEFT JOIN user AS t2 ON t1.teamleadID = t2.id
                  LEFT JOIN user AS t3 ON t1.memberID = t3.id
				  LEFT JOIN designation AS t4 ON t3.position = t4.id
				  WHERE t1.id<>'0'";

	empty($tid) ? NULL : $statement .= " AND t1.teamleadID = '$tid'";
	empty($mid) ? NULL : $statement .= " AND t1.memberID = '$mid'";
	//empty($from_date) ? NULL : $statement .= " AND t1.from_date >= '$from_date'";
	//empty($to_date) ? NULL : $statement .= " AND t1.to_date <= '$to_date'";	
	is_null($category) ? NULL : $statement .= " AND t1.category = '$category'";
	is_null($status) ? NULL : $statement .= " AND t1.status = '$status'";

	$users = $db->result_all("SELECT t1.id AS kpi_id, t1.teamleadID, t1.memberID, t1.category, t1.status, t1.from_date, t1.to_date, 
                                     CONCAT(t2.fname, ' ', t2.lname) AS teamlead_fullname, 
                                     t3.fname AS member_fname, t3.lname AS member_lname, t3.eid, t4.name AS designationName
                             		 FROM {$statement} order by t2.fname LIMIT {$startpoint}, {$per_page}");

	if (!empty($users)) {
		foreach ($users as $data) {
			$user_info[$data->teamlead_fullname][] =  $data;
		}
	}

	$count_query = "SELECT COUNT(DISTINCT t1.teamleadID) as num FROM {$statement}";
	$total_active_teamsetup = $db->result_one($count_query);
	$total_active_teamsetup = $total_active_teamsetup->num ?? 0;
}

?>

<div id="page-wrapper">
	<div class="row print_div" id="print-div1">
		<div class="col-lg-12">
			<?php echo $flash->display(); ?>
			<div class="panel panel-info">
				<div class="panel-heading avoid">
					KPI Team Setup List
					<?php if (isset($total_active_teamsetup)) { ?>
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
						<input type="hidden" name="page" value="teamsetup-list" />
						<div class="row avoid">
							<div class="searchFieldWrapper clearfix">

								<div class="col-md-4 col-sm-6">
									<div class="form-group">
										<label>Team Leader</label>
										<input type="hidden" id="searchType" value="employee" />
										<select name="teamleadID" id='contact_ajax_search' class="form-control">
											<?php if (isset($_GET['teamleadID']) && !empty($_GET['teamleadID'])) { ?>
												<option value='<?php echo $_GET['teamleadID']; ?>'><?php echo User::user_return($_GET['teamleadID'])["FullName"]; ?></option>
											<?php } else {
												echo "<option value=''>- Search Teamleader Name -</option>";
											} ?>
										</select>
									</div>
								</div>

								<div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Member</label>
                                        <input type="hidden" id="search_Type2" value="employee" />
                                        <select name="memberID" id='contact_ajax_search2' class="form-control ">
                                            <?php if (isset($_GET['memberID']) && !empty($_GET['memberID'])) { ?>
                                                <option value='<?php echo $_GET['memberID']; ?>'><?php echo User::user_return($_GET['memberID'])["FullName"]; ?></option>
                                                
                                            <?php } else {
                                                echo "<option value=''>- Search Member Name -</option>";
                                            } ?>
                                        </select>
                                    </div>
                                </div>

								<!-- <div class="col-lg-4 col-sm-6">
										<div class="form-group"> 
											<label>From date</label>
												<input type="text" class="form-control" name="from_date" value=""  id="date1" placeholder="From Date" />
											</div>
									</div>

									<div class="col-lg-4 col-sm-6">
										<div class="form-group">
										<label>To date</label>
											<input type="text" class="form-control" name="to_date" value=""  id="date2" placeholder="To Date" />
										</div>
									</div> -->

								<div class="col-lg-2 col-sm-6">
									<div class="form-group">
										<label>Category</label>
										<select class="form-control" id="category" name="category">
											<option value=""> All category </option>
											<option value="1"> Main </option>
											<option value="0"> Optional </option>
										</select>
									</div>
								</div>

								<div class="col-lg-1 col-sm-6">
									<div class="form-group">
										<label>Status</label>
										<select class="form-control" id="status" name="status">
											<option value="1"> Active </option>
											<option value=""> All </option>
											<option value="0"> Inactive </option>
										</select>
										</label>
									</div>
								</div>

								<div class="col-lg-1 col-sm-6">
									<div class="form-group" style="margin-top: 25px;">
										<input type="submit" name="search_teamsetup" class="btn-new avoid" value="Search">
									</div>
								</div>

							</div>
						</div>
					</form>

					<!-- Custom Search Field For Print View Starts -->
					<div class="quickInfoSectionWrapper">
						<?php
						
						if (isset($total_active_teamsetup)) {
						?>
							<div class="row">
								<div class="searchQuickInfoSectionWrapper">
									<div class="col-md-4 col-lg-4">
										<div class="searchQuickInfoSection quickInfoDataTable">
											<div class="sectionLeft">
												<i class="fas fa-users"></i> Total Team
											</div>
											<div class="sectionRight">
												<?php
												echo $total_active_teamsetup;
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- Search Quick Info Section Ends -->

							<div class="table-responsive">
								<table class="table table-striped employee-list-table" id="">
									<thead>
										<tr>
											<th width="4%">SN</th>
											<th class="text-left">Teams</th>
											<th class="text-left">CID</th>
											<th class="text-left">Designation</th>
											<th class="text-left">Category</th>
											<th class="text-left">Status</th>
											<th class="text-left">From Date</th>
											<th class="text-left">To Date</th>
											<th class="avoid text-center">Update</th>
										</tr>
									</thead>

									<tbody>
										<?php
											$sn = ($page - 1) * $per_page + 1;
																				
											if (!empty($user_info)) {
												foreach ($user_info as $teamlead_name => $rows) {
													$teamleadID = $rows[0]->teamleadID ?? ''; 
											?>
												<tr>
													<td colspan="8" class="text-left" style="font-weight: bold;"><?php echo "{$teamlead_name}"; ?></td>
													<td class="avoid text-center">
														<div class="buttonWrapper">
															<a class="btn-updates" href="index.php?page=teamsetup&teamleadID=<?php echo "{$teamleadID}"; ?>" target="_blank" title="Edit Profile">
																<i class="fas fa-edit"></i>
															</a>
														</div>
													</td>
												</tr>
												<?php
													foreach ($rows as $row) { ?>
													<tr class="gradeA">
														<td><?php echo  $sn++; ?></td>
														<td class="text-left"><?php echo trim(($row->member_fname ?? '') . ' ' . ($row->member_lname ?? '')); ?></td>
														<td class="text-left"><?php echo ($row->eid); ?></td>
														<td class="text-left"><?php echo ($row->designationName); ?></td>
														<td class="text-left">
															<?php echo ($row->category == 1) ? 'Main' : 'Optional'; ?>
														</td>
														<td class="text-left">
															<?php echo ($row->status == 1) ? 'Active' : 'Inactive'; ?>
														</td>
														<td class="text-left"><?php echo return_date($row->from_date); ?></td>
														<td class="text-left"><?php echo return_date($row->to_date); ?></td>
														<td></td>
													</tr>
											<?php   } 
												}
											} else { ?>
											<tr>
												<td colspan="16" class="text-center">No results found for the given filters.</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
							<?php echo '<div class="row avoid">';
							echo '<div class="col-md-10 col-xs-12 col-md-offset-2">';
							echo  $db->pagination($statement, $per_page, $page, curPageURL());
							?>
					</div>
				</div>
				<!-- /.table-responsive -->
				<?php
							// }			
				?>
			</div>
			<!-- /.panel-body -->
		</div>
	</div>
	<!-- /.panel -->
<?php
						}
?>
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