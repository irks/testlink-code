<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqEdit.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2007/11/25 18:59:40 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Screen to view existing requirements within a req. specification.
 * 
 * rev: 20070415 - franciscom - custom field manager
 *      20070415 - franciscom - added reorder feature
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once('attachments.inc.php');
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once('requirement_spec_mgr.class.php');
require_once('requirement_mgr.class.php');

require_once("../../third_party/fckeditor/fckeditor.php");
require_once("configCheck.php");
testlinkInitPage($db);

$req_spec_mgr=new requirement_spec_mgr($db);
$req_mgr=new requirement_mgr($db);

$get_cfield_values=array();
$get_cfield_values['req_spec']=0;
$get_cfield_values['req']=0;

$user_feedback='';
$sqlResult = null;
$action = null;
$sqlItem = 'SRS';
$arrReq = array();
$template_dir="requirements/";
$template='reqSpecView.tpl';

$main_descr=null;
$action_descr=null;
$cf_smarty=null;

$_REQUEST = strings_stripSlashes($_REQUEST);
$args=init_args();

$tproject = new testproject($db);
$smarty = new TLSmarty();

$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = $g_fckeditor_toolbar;;

switch($args->do_action)
{
  case "create":
  $template = $template_dir . 'reqEdit.tpl';
  $req_spec=$req_spec_mgr->get_by_id($args->req_spec_id);
  $main_descr=lang_get('req_spec') . TITLE_SEP . $req_spec['title'];
  $action_descr=lang_get('create_req');
	
	// get custom fields
	$cf_smarty = $req_mgr->html_table_of_custom_field_inputs(null,$args->tproject_id);
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');
  break;


  case "edit":
  $template = $template_dir . 'reqEdit.tpl';
  $req = $req_mgr->get_by_id($args->req_id);
  $main_descr=lang_get('req') . TITLE_SEP . $req['title'];
  $action_descr=lang_get('edit_req');


	// get custom fields
	$cf_smarty = $req_mgr->html_table_of_custom_field_inputs($args->req_id,$args->tproject_id);
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_update');
  $smarty->assign('req', $req); 
  break;


  case "do_create":
  $req_spec=$req_spec_mgr->get_by_id($args->req_spec_id);
  $main_descr=lang_get('req_spec') . TITLE_SEP . $req_spec['title'];
  $action_descr=lang_get('create_req');

	$template = $template_dir . 'reqEdit.tpl';
	$cf_smarty = $req_mgr->html_table_of_custom_field_inputs(null,$args->tproject_id);
	$smarty->assign('cf',$cf_smarty);
	$ret = $req_mgr->create($args->req_spec_id,$args->reqDocId,$args->title,$args->scope,$args->user_id,
	                        $args->reqStatus,$args->reqType);
	$user_feedback = $ret['msg'];	                                 
	if($ret['status_ok'])
	{
		$user_feedback = sprintf(lang_get('req_created'), $args->reqDocId);  
	  $cf_map = $req_mgr->get_linked_cfields(null,$args->tproject_id) ;
    $req_mgr->values_to_db($_REQUEST,$ret['id'],$cf_map);
	}
  $args->scope = '';
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');
  break;


  case "do_update":
  $template = $template_dir . 'reqView.tpl';
  $ret = $req_mgr->update($args->req_id,trim($args->reqDocId),$args->title,
                          $args->scope,$args->user_id,$args->reqStatus,$args->reqType);
	                              
  if( $ret['status_ok'] )
	{
    $cf_map = $req_mgr->get_linked_cfields(null,$args->tproject_id) ;
    $req_mgr->values_to_db($_REQUEST,$args->req_id,$cf_map);
  }
  
	$cf_smarty = $req_mgr->html_table_of_custom_field_values($args->req_id,$args->tproject_id);
  $req = $req_mgr->get_by_id($args->req_id);
  $smarty->assign('req', $req); 
  $main_descr=lang_get('req') . TITLE_SEP . $req['title'];
  break;
    
    
  case "do_delete":
  $template = 'show_message.tpl';

  $req = $req_mgr->get_by_id($args->req_id);
  $req_mgr->delete($args->req_id);

  $user_feedback = sprintf(lang_get('req_deleted'),$req['title']);
  $smarty->assign('title', lang_get('delete_req'));
  $smarty->assign('item_type', lang_get('requirement'));
  $smarty->assign('item_name', $req['title']);
  $smarty->assign('user_feedback',$user_feedback );
  $smarty->assign('refresh_tree','yes');
  $smarty->assign('result','ok');
  break;
  
    
    
}

$smarty->assign('cf',$cf_smarty);
$smarty->assign('action_descr',$action_descr);
$smarty->assign('main_descr',$main_descr);
$smarty->assign('req_id', $args->req_id);
$smarty->assign('req_spec_id', $args->req_spec_id);
$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('action', $action);
$smarty->assign('name',$args->title);
$smarty->assign('selectReqStatus', $arrReqStatus);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 

$of->Value="";
if (!is_null($args->scope))
{
	$of->Value=$args->scope;
}

$smarty->assign('scope',$of->CreateHTML());
$smarty->display($template);
?>


<?php
function init_args()
{
  $args->req_id = isset($_REQUEST['requirement_id']) ? $_REQUEST['requirement_id'] : null;
  $args->req_spec_id = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : null;
  $args->reqDocId = isset($_REQUEST['reqDocId']) ? trim($_REQUEST['reqDocId']) : null;
  $args->title = isset($_REQUEST['req_title']) ? trim($_REQUEST['req_title']) : null;
  $args->scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
  $args->reqStatus = isset($_REQUEST['reqStatus']) ? $_REQUEST['reqStatus'] : TL_REQ_STATUS_VALID;
  $args->reqType = isset($_REQUEST['reqType']) ? $_REQUEST['reqType'] : TL_REQ_TYPE_1;
  $args->countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;

  $args->do_action = isset($_REQUEST['do_action']) ? $_REQUEST['do_action']:null;
  $args->do_export = isset($_REQUEST['exportAll']) ? 1 : 0;
  $args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;
  $args->do_create_tc_from_req = isset($_REQUEST['create_tc_from_req']) ? 1 : 0;
  $args->do_delete_req = isset($_REQUEST['req_select_delete']) ? 1 : 0;
  $args->reorder = isset($_REQUEST['req_reorder']) ? 1 : 0;
  $args->do_req_reorder = isset($_REQUEST['do_req_reorder']) ? 1 : 0;

  $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
  $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
  
  return $args;
}
?>