<?php

$version = "ezgenerator centraladmin V4 - 5.2.0";
/*
  centraladmin.php
  http://www.ezgenerator.com
  Copyright (c) 2004-2014 Image-line
 */
$ca_pref = (is_dir('ezg_data')) ? '' : '../';
include_once($ca_pref . 'ezg_data/functions.php');
$ca_admin_username = "admin";
$ca_admin_pwd = "21232f297a57a5a743894a0e4a801fc3";
$ca_db_dir = $ca_pref . $f->db_folder;
$ca_db_file = $ca_db_dir . 'centraladmin.ezg.php';
$ca_db_settings_file = $ca_db_dir . 'centraladmin_conf.ezg.php'; // settings file --> counter,self-reg and other settings
$ca_db_activity_log = $ca_db_dir . 'centraladmin_reglog.ezg.php';
$ca_db_delay_file = $ca_db_dir . 'centraladmin_sec.ezg.php';
$counter_ts_db_fname = $ca_db_dir . 'counter_totals_db.ezg.php';
$counter_ds_db_fname = $ca_db_dir . 'counter_db.ezg.php';
$ca_lang_set_fname = $ca_pref . 'ezg_data/ca_lang_set.txt';
$rss_call_in_prot_page = false;
if (isset($thispage_id) && isset($_GET['action']) && $_GET['action'] == 'rss')
	 $rss_call_in_prot_page = true; // public rss when page is protected
if (!isset($thispage_id)) {
	 $thispage_id = (isset($_GET['pageid']) ? intval($_GET['pageid']) : '');
}

$ca_myprofile_actions = array('changepass', 'editprofile', 'myprofile');
$ca_admin_actions = array("index", "manageusers", "processuser", "confcounter", "resetcounter", "log", "maillog", "clearlog", "clearmaillog", "confreg", "pendingreg", "conflang", "export", "confreglang", 'login', 'toggleFold');
$ca_other_actions = array_merge(array("logout", "logoutadmin", "version", "register", "register2", "captcha", "loggedinfo", "loggeduser", "logged", "logoutinfo", "forgotpass", "forgotpass2", "sitemap", "prev", "next"), $ca_myprofile_actions);
$ca_user_actions = array("changepass", "editprofile", "myprofile", 'register', 'register2', "forgotpass", "forgotpass2", 'login');
$ca_pref_dir = '../documents/';
$template_in_root = false;
$ca_template_file = Detector::defineSourcePage($ca_pref, (isset($_GET['lang']) ? strtoupper(Formatter::stripTags($_GET['lang'])) : ''));
if (strpos($ca_template_file, '/') === false) {
	 $ca_template_file = '../' . $ca_template_file;
	 $template_in_root = true;
	 $ca_pref_dir = 'documents/';
}

$sr_enable = false;
$sr_notif_enabled = true;
$f->ca_settings = File::read($ca_db_settings_file);
$ca_reg_lang_settings_keys = array('sr_email_subject', 'sr_email_msg', 'sr_notif_subject', 'sr_forgotpass_subject0', 'sr_forgotpass_msg0', 'sr_forgotpass_subject', 'sr_forgotpass_msg', 'sr_activated_subject', 'sr_activated_msg', 'sr_blocked_subject', 'sr_blocked_msg');
$ca_reg_lang_settings_labels = array('registration email subject', 'registration email message', 'registration notification subject', 'forgot password confirm email subject', 'forgot password confirm email message', 'forgot password email subject', 'forgot password email message', 'activation email subject', 'activation email message', 'blocked account email subject', 'blocked account email message');
$ca_lang_l = array();
$ca_lang = '';
$ca_action_id = '';
$ca_l = '';
$ca_l_amp = '';
$ca_access_type = array();
$ca_access_type_ex = array();
$ca_ulang_id = 0;	  //user interface current language
$ca_account_msg = '<div align="left">' . F_BR . '<span class="rvts4"><em style="color:red;">Username & Password are not set for your Online Administration account.</em></span> ' . F_BR . F_BR . '<span class="rvts8">To SOLVE the problem, go to <em style="color:red;">EZGenerator -> Project Settings - Online Administration</em> and set <em style="color:red;">Username & Password</em>.</div>';
$ca_mail_msg = '<div align="left">' . F_BR . '<span class="rvts4"><em style="color:red;">Admin e-mail address not defined.</em></span> ' . F_BR . F_BR . '<span class="rvts8">To SOLVE the problem, go to <em style="color:red;">Online Administration >> Registration Settings</em> and define <em style="color:red;">Admin Email!</em></span>';
$ca_span8 = '<span class="rvts8">';
$ca_sitemap_arr = CA::getSitemap($ca_pref);
$ca_site_url = str_replace('documents/centraladmin.php', '', Linker::buildSelfURL('centraladmin.php'));
$ca_areaarray = array();
$ca_loginids = array();
$ca_earea_pages = array();
$ca_allunamechars = "/^[A-Za-z_.@0-9-]+$/";

function f_has_read_access($user_account, $prot_page_info) {
	 global $f;

	 $auth = $section_flag = $write_flag = $access = false;
	 $page_id = str_replace('<id>', '', $prot_page_info[10]);

	 if (!empty($user_account) && ($user_account['status'] == '1') && isset($prot_page_info[1])) {
		  if (isset($user_account['access'][0]) && $user_account['access'][0]['section'] != 'ALL') {
				foreach ($user_account['access'] as $v) {
					 if ($prot_page_info[7] == $v['section'] || !$f->use_prot_areas) {
						  $section_flag = true;
						  if ($prot_page_info[7] == $v['section'] && $v['type'] == '0')
								$access = true;
						  elseif ($prot_page_info[7] == $v['section'] && $v['type'] == '1') {
								$write_flag = true;
								$access = true;
						  } elseif ($v['type'] == '2' && isset($v['page_access'])) {
								foreach ($v['page_access'] as $val) {
									 if ($page_id == $val['page']) {
										  if ($val['type'] == '1' || $val['type'] == '3') {
												$write_flag = true;
												$access = true;
												break;
										  } elseif ($val['type'] == '0') {
												$access = true;
												break;
										  } elseif ($val['type'] == '2')
												break;
									 }
								}
						  }
						  if ($prot_page_info[7] == $v['section'])
								break;
					 }
				}
		  }
		  else {
				$section_flag = true;
				$access = true;
				if (isset($user_account['access'][0]) && $user_account['access'][0]['type'] == '1')
					 $write_flag = true;
		  }
		  if ($section_flag)
				$auth = $write_flag || (!isset($_GET['indexflag']) && $access);
	 }
	 return $auth;
}

function ca_getformbuttons($btn1_name = 'save', $cancel = true, $cancel_action = 'onclick="javascript:history.back();"') {
	 global $ca_lang_l;
	 $res = '<input name="' . $btn1_name . '" type="submit" value="' . $ca_lang_l['save'] . '">';
	 if ($cancel)
		  $res.='<input type="button" value=" ' . $ca_lang_l['cancel'] . ' " ' . $cancel_action . '>';
	 return $res;
}

function ca_update_language_set($new_lang = '') {
	 global $user, $ca_action_id, $f, $ca_lang, $ca_lang_l, $ca_lang_set_fname, $ca_reg_lang_settings_keys,
	 $ca_l, $ca_l_amp, $ca_access_type, $ca_access_type_ex, $thispage_id, $ca_pref, $ca_ulang_id, $ca_myprofile_actions,
	 $ca_lang, $ca_pref;

	 $user_actions = array_merge($ca_myprofile_actions, array('forgotpass', 'register', 'forgotpass2', 'register2'));
	 $ca_lang = Formatter::GFS($f->ca_settings, '<language>', '</language>');
	 if (in_array($ca_action_id, $ca_myprofile_actions)) {
		  Session::intStart('private');
		  if ($user->userCookie())
				$logged_user = $user->getUserCookie();
	 }
	 if (!empty($new_lang)) {
		  $ca_lang = strtoupper($new_lang);
		  $rl_flag = true;
	 } elseif (isset($_REQUEST['lang']) && in_array($ca_action_id, $user_actions)) {
		  $ca_lang = strtoupper(Formatter::stripTags($_REQUEST['lang']));
		  $rl_flag = true;
	 } elseif (isset($logged_user) && isset($_COOKIE[$logged_user . '_lang'])) {
		  $ca_lang = strtoupper(Formatter::stripTags($_COOKIE[$logged_user . '_lang']));
		  $rl_flag = true;
	 } elseif (isset($_COOKIE['ca_lang']))
		  $ca_lang = strtoupper(Formatter::stripTags($_COOKIE['ca_lang']));
	 elseif ($ca_lang != '')
		  $ca_lang = strtoupper($ca_lang);
	 elseif (isset($_REQUEST['lang']))
		  $ca_lang = strtoupper(Formatter::stripTags($_REQUEST['lang']));
	 else {
		  $sitemap_data = CA::getSitemap($ca_pref, false);
		  $ca_lang = $f->inter_languages_a[array_search($sitemap_data[0][16], $f->site_languages_a)];
	 }

	 if (!array_key_exists($ca_lang, $f->names_lang_sets))
		  $ca_lang = 'EN';

	 $lang_set_updated = File::readLangSet($ca_lang_set_fname, $ca_lang, 'ca');
	 if (isset($lang_set_updated['lang_l']))
		  $ca_lang_l = $lang_set_updated['lang_l'];
	 $reg_lang_set_raw = Formatter::GFS($f->ca_settings, '<sr_language_' . $ca_lang . '>', '</sr_language_' . $ca_lang . '>');
	 if ($reg_lang_set_raw != '') {
		  foreach ($ca_reg_lang_settings_keys as $v) {
				if (strpos($reg_lang_set_raw, '<' . $v . '>') !== false)
					 $ca_lang_l[$v] = Formatter::unEsc(Formatter::GFS($reg_lang_set_raw, '<' . $v . '>', '</' . $v . '>'));
		  }
	 }
	 $ca_l = (isset($_GET['lang']) && $ca_lang != 'EN' ? '&lang=' . $ca_lang : '');
	 $ca_l_amp = (isset($_GET['lang']) && $ca_lang != 'EN' ? '&amp;lang=' . $ca_lang : '');
	 $ca_access_type = array('0' => $ca_lang_l['view'], '1' => $ca_lang_l['edit']);
	 $ca_access_type_ex = array('0' => $ca_lang_l['view'], '1' => $ca_lang_l['edit'], '2' => $ca_lang_l['page level'], '-1' => 'no access');
	 $ca_ulang_id = array_search($ca_lang, $f->inter_languages_a);
	 $myprofile_labels = CA::getMyprofileLabels($thispage_id, $ca_pref, (isset($rl_flag) ? $ca_lang : ''));
	 $ca_lang_l = array_merge($ca_lang_l, $myprofile_labels);
}

function un_esc($s) {
	 return htmlspecialchars(str_replace(array('\\\\', '\\\'', '%%%'), array('\\', '\'', '"'), $s), ENT_QUOTES);
}

function esc($s) {
	 return (get_magic_quotes_gpc() ? str_replace('\"', '%%%', $s) : str_replace(array('\\', '\'', '"'), array('\\\\', '\\\'', '%%%'), $s));
}

function get_page_info($page_id) { // gets info for protected page
	 global $ca_sitemap_arr, $thispage_id, $f;

	 $forms = array_merge($f->subminiforms, $f->subminiforms_news);
	 $page = array();
	 if (array_key_exists($page_id, $forms) || ($page_id == 0 && isset($_GET['pageid']) && array_key_exists($_GET['pageid'], $forms))) {
		  $page_id = $forms[isset($_GET['pageid']) ? $_GET['pageid'] : $page_id];
	 }

	 foreach ($ca_sitemap_arr as $k => $v) {
		  if ($v[10] == '<id>' . $page_id) {
				$page = $v;
				break;
		  }
	 }
	 if (empty($page)) {
		  if ($thispage_id == $page_id) {
				if (isset($_POST['loginid'])) {
					 foreach ($ca_sitemap_arr as $k => $v) {
						  if (isset($v[10]) && Validator::checkProtection($v) > 1 && $v[7] == Formatter::stripTags($_POST['loginid'])) {
								$page = $v;
								break;
						  }
					 }
					 if (empty($page)) {
						  foreach ($ca_sitemap_arr as $k => $v) {
								if (isset($v[10]) && Validator::checkProtection($v) > 1 && $v[4] == '136') {
									 $page = $v;
									 break;
								}
						  }
					 }
					 if (empty($page)) {
						  print GT(F_BR . "<span class='rvts8'><b>The system doesn't know where to redirect you.");
						  exit;
					 }
				}
		  }
	 }
	 return $page;
}

function get_pages_list($type_id = '', $lang = '') {
	 global $f, $ca_pref;

	 $pages = array();
	 $ca_sitemap_arr_cats_incl = CA::getSitemap($ca_pref, true);
	 $cat_counter = 1;
	 foreach ($ca_sitemap_arr_cats_incl as $v) {
		  $buffer = array();
		  $p_name = strpos($v[0], '#') !== false && strpos($v[0], '#') == 0 ? str_replace('#', '', $v[0]) : $v[0];
		  if (isset($v[10]) && strpos($v[10], '<id>') !== false && ($lang == '' || $v[22] == $lang)) {
				$buffer['name'] = trim($p_name);
				$buffer['id'] = trim($v[4]);
				$buffer['url'] = $v[1];
				$prot = Validator::checkProtection($v);
				$buffer['protected'] = $prot > 1 ? 'TRUE' : 'FALSE';
				$buffer['pprotected'] = ($prot == 3); //could be used in future
				$buffer['hidden'] = $v[20];
				$buffer['section'] = $v[7];
				$buffer['subpage'] = $v[3];
				$buffer['subpage_url'] = $v[18];
				$buffer['lang'] = $v[16];
				$p_id = str_replace('<id>', '', $v[10]);
				$buffer['pageid'] = $p_id;
				$buffer['editable'] = $v[23];
				if (in_array($v[4], $f->sp_pages_ids) || $v[23] == 'TRUE')
					 $buffer ['adminurl'] = CA::defineAdminLink($v);
		  }
		  else {
				$p_id = 'ct_' . $cat_counter++;
				$buffer = array('name' => trim($p_name));
		  }

		  if ($type_id == '' || isset($buffer['id']) && $buffer['id'] == $type_id)
				$pages[$p_id] = $buffer;
	 }
	 return $pages;
}

function get_prot_pages_list($section_id = '', $include_editable = false) {
	 global $ca_sitemap_arr, $f, $ca_earea_pages;

	 $forms = array_merge($f->subminiforms, $f->subminiforms_news);
	 $pages = array();
	 foreach ($ca_sitemap_arr as $v) {
		  if (!empty($v) && strpos($v[10], '<id>') !== false) {
				$p_id = str_replace('<id>', '', $v[10]);
				$p_name = strpos($v[0], '#') !== false && strpos($v[0], '#') == 0 ? str_replace('#', '', trim($v[0])) : trim($v[0]);
				$ca_control = (in_array($v[4], $f->sp_pages_ids) || Validator::checkProtection($v) > 1 || (in_array($p_id, $ca_earea_pages)) || ($include_editable && $v[23] == 'TRUE') || in_array($p_id, $forms));
				if ($ca_control && ($section_id === '' || $v[7] == $section_id)) {
					 $temp = array('name' => $p_name, 'url' => $v[1], 'typeid' => $v[4], 'section' => $v[7], 'protected' => (Validator::checkProtection($v) > 1 ? 'TRUE' : 'FALSE'), 'id' => $p_id);
					 $pages[] = $temp;
				}
		  }
	 }
	 return $pages;
}

function duplicated_user($user) {
	 global $ca_admin_username;

	 $existing_users = db_get_users();
	 $selfreg_users = db_get_users('selfreg_users');

	 $user = strtolower($user);
	 if (strtolower($ca_admin_username) == $user)
		  return true;
	 if (strpos(strtolower($existing_users), 'username="' . $user . '"') !== false)
		  return true;
	 elseif (strpos(strtolower($selfreg_users), 'username="' . $user . '"') !== false)
		  return true;
	 else
		  return false;
}

function error($id, $delay, $user_account = array()) {
	 global $ca_lang_l, $f, $ca_lang;

	 if ($delay)
		  set_delay();
	 $ca_miniform = (isset($_GET['pageid']) && !isset($_POST['loginid']) && !isset($_GET['indexflag']) && !isset($_GET['ref_url']));
	 $ccheck = (isset($_POST['cc']) && $_POST['cc'] == '1');
	 $useic = (!$f->uni && $f->charset_lang_map[$ca_lang] != 'iso-8859-1' && function_exists("iconv"));

	 $contents = '';
	 $issues = array();
	 if (isset($_GET['ref_url']) && $_GET['ref_url'] != '')
		  $contents = build_login_form('', Formatter::stripTags($_GET['ref_url']), $user_account); //event manager
	 elseif (!$ca_miniform)
		  $contents = build_login_form('', '', $user_account);

	 $err_msg = $ca_lang_l['use correct username'];
	 $issues[] = 'error|' . $err_msg;
	 if (!empty($user_account) && $user_account['status'] == '0') {
		  $err_msg = $ca_lang_l['blocked_err_msg'];
		  $issues[] = 'error|' . $err_msg;
	 }

	 if ($ccheck) {
		  $errors_output = implode('|', $issues);
		  if ($useic)
				$errors_output = iconv($f->charset_lang_map[$ca_lang], "utf-8", $errors_output);

		  if (count($issues) > 0)
				print '0' . $errors_output;
		  else
				print '1';
		  exit;
	 }
	 $contents = str_replace('<!--page-->', '<!--page-->' . '<div class="rvps1"><h5>' . $err_msg . F_BR . F_BR . '</h5></div>', $contents);
	 print $contents;
	 exit;
}

//admin
function index() { // site map screen
	 global $f, $counter_ts_db_fname, $ca_pref_dir, $template_in_root, $ca_lang_l, $ca_l, $ca_span8, $ca_site_url;

	 $output = '';
	 $menu_title = ' - ' . $ca_lang_l['graph stat'] . ' ';

	 if (isset($_GET['stat']))
		  $output = ($_GET['stat'] == 'detailed') ? detailed_stats(true, $menu_title) : old_detailed_stats($menu_title);
	 else {
		  $pages_list = get_pages_list();
		  $counter_stat = File::readTaggedData($counter_ts_db_fname, 'totals'); // counter data
		  $rss_count = (strpos($f->ca_settings, '<rss_count>') !== false) ? Formatter::GFS($f->ca_settings, '<rss_count>', '</rss_count>') : '0';

		  $cap_arrays = array($ca_lang_l['page name'], $ca_lang_l['admin link'], $ca_lang_l['protected']);
		  if ($f->counter_on) {
				$cap_arrays[] = $ca_lang_l['pageloads'];
				$cap_arrays[] = $ca_lang_l['unique visitors'];
				if ($rss_count == '1')
					 $cap_arrays[] = $ca_lang_l['rssloads'];
		  }
		  $table_data = array();

		  $lang_flag = '';
		  foreach ($pages_list as $k => $v) {
				$page_text = $admin_text = $prot_text = '';
				$counter_text = '';
				if (!empty($v['name']) && !isset($v['id']) && isset($pages_list[$k + 1]['lang']) && $pages_list[$k + 1]['lang'] != $lang_flag) {
					 $row_data = '<span class="a_lang_label">' . $pages_list[$k + 1]['lang'] . '</span>';
					 $table_data[] = $row_data;
					 $lang_flag = $pages_list[$k + 1]['lang'];
				}

				if (isset($v['id'])) {
					 if ($template_in_root) {
						  $v_url = str_replace('../', '', $v['url']);
						  $supage_url = str_replace('../', '', $v['subpage_url']);
					 } else {
						  $v_url = (strpos($v['url'], '../') === false ? '../' : '') . $v['url'];
						  $supage_url = (strpos($v['subpage_url'], '../' === false) ? '../' : '') . $v['subpage_url'];
					 }
					 $page_text.=$ca_span8 . (($v['subpage'] == '1') ? '&nbsp;&nbsp;&nbsp;&nbsp;- ' : ':: ') . '</span>';
					 $page_text.='<a target="_blank" class="rvts8 nodec" href="' . $v_url . '">' . $v['name'] . "</a>";

					 if (in_array($v['id'], $f->sp_pages_ids) || $v['editable'] == 'TRUE') {
						  if ($template_in_root)
								$admin_url = str_replace('../', '', $v['adminurl']);
						  else
								$admin_url = (strpos($v['adminurl'], '../') === false) ? '../' . $v['adminurl'] : $v['adminurl'];
						  $admin_text.=$ca_span8 . "[</span><a class='rvts12' href='" . $admin_url . $ca_l . "'>";
						  $admin_text.=$ca_lang_l['edit'] . "</a>" . $ca_span8 . "]</span>";
					 }
					 $prot_text = ($v['protected'] == 'TRUE' ? $ca_span8 . '[X]</span>' : '');
					 $row_data = array($page_text, $admin_text, $prot_text);
					 if ($f->counter_on) {
						  $pg_loads = get_loads($counter_stat, $v['pageid'], $v_url, $v['name']);
						  $row_data[] = $pg_loads;
						  $row_data[] = (strpos($pg_loads, $ca_lang_l['na']) === false) ? ('<span class="rvts8">[</span><a class="rvts12" href="' . $ca_pref_dir . 'centraladmin.php?process=index&stat=detailed' . $ca_l . '&pid=' . $v['pageid'] . '&f=u&purl=' . $v_url . '&pname=' . $v['name'] . '">' . $ca_lang_l['details'] . $ca_span8 . '</a><span class="rvts8">]</span>') : '';

						  $rss_loads = get_loads($counter_stat, $v['pageid'], $v_url, $v['name'], true);
						  $alr = '<div style="text-align:right;">';
						  if (strpos($ca_site_url, 'image-line.com') !== false) {
								$rss_line = $alr . (strpos($rss_loads, $ca_lang_l['na']) === false ? ' <span class="rvts8">[</span><a class="rvts12" href="' . $ca_pref_dir . 'centraladmin.php?process=index&stat=detailed' . $ca_l . '&pid=' . $v['pageid'] . '&f=hrss&purl=' . $v_url . '&pname=' . $v['name'] . '">' . $ca_lang_l['details'] . $ca_span8 . '</a><span class="rvts8">]</span>' : '') . '</div>';
								$loads = get_loads($counter_stat, $v['pageid'], $v_url, $v['name'], true, 'fl');
								if ($loads != '')
									 $rss_line.=$alr . '<span class="rvts8">FL direct [</span><a class="rvts12" href="' . $ca_pref_dir . 'centraladmin.php?process=index&stat=detailed' . $ca_l . '&pid=' . $v['pageid'] . '&f=flrss&purl=' . $v_url . '&pname=' . $v['name'] . '">' . $ca_lang_l['details'] . $ca_span8 . '</a><span class="rvts8">]</span></div>';
						  } else
								$rss_line = $alr . $rss_loads . (strpos($rss_loads, $ca_lang_l['na']) === false ? ' <span class="rvts8">[</span><a class="rvts12" href="' . $ca_pref_dir . 'centraladmin.php?process=index&stat=detailed' . $ca_l . '&pid=' . $v['pageid'] . '&f=hrss&purl=' . $v_url . '&pname=' . $v['name'] . '">' . $ca_lang_l['details'] . $ca_span8 . '</a><span class="rvts8">]</span>' : '') . '</div>';

						  if ($rss_count == '1')
								$row_data[] = $rss_line;
					 }
					 $table_data[] = $row_data;
				}
				elseif (!empty($v['name'])) {
					 $row_data = '<span class="a_lang_label">' . $v['name'] . '</span>';
					 $table_data[] = $row_data;
				}
		  }

		  if (!empty($f->subminiforms)) { //miniforms
				$table_data[] = '&nbsp;';
				foreach ($f->subminiforms as $f->id => $p_id) {
					 $is_page = ($p_id > 0 && isset($pages_list[$p_id]));
					 $page_url = $is_page ? $pages_list[$p_id]['url'] : 'javascript:void(0);';
					 $sub_parent_url = ($p_id == '0') ? '../documents/' : ((strpos($page_url, '../') === false) ? '../' : '../' . Formatter::GFS($page_url, '../', '/') . '/');
					 $sub_dir = ($template_in_root) ? str_replace('../', '', $sub_parent_url) : ((strpos($sub_parent_url, '../') === false ? '../' : '') . $sub_parent_url);
					 $sub_url = 'href="' . $sub_dir . 'ezgmail_' . $p_id . '_' . $f->id . '.php?action=index';
					 $page_text = $ca_span8 . ':: </span><a class="rvts8 nodec" ' . $sub_url . '">' . $ca_lang_l['request'] . '(' . $f->id . ')</a>';
					 $admin_text = '<span class="rvts8">[</span><a class="rvts12" ' . $sub_url . '">' . $ca_lang_l['edit'] . '</a><span class="rvts8">]</span>';
					 $row_data = array($page_text, $admin_text, '');
					 if ($f->counter_on) {
						  $row_data[] = '';
						  if ($rss_count == '1')
								$row_data[] = '';
						  $row_data[] = '';
					 }

					 $table_data[] = $row_data;
				}
		  }

		  if (!empty($f->subminiforms_news)) { //miniforms
				$table_data[] = '&nbsp;';
				foreach ($f->subminiforms_news as $f->id => $p_id) {
					 $is_page = ($p_id > 0 && isset($pages_list[$p_id]));
					 $page_url = $is_page ? $pages_list[$p_id]['url'] : 'javascript:void(0);';
					 $sub_parent_url = ($p_id == '0') ? '../documents/' : ((strpos($page_url, '../') === false) ? '../' : '../' . Formatter::GFS($page_url, '../', '/') . '/');
					 $sub_dir = ($template_in_root) ? str_replace('../', '', $sub_parent_url) : ((strpos($sub_parent_url, '../') === false ? '../' : '') . $sub_parent_url);
					 $sub_url = 'href="' . $sub_dir . 'newsletter_' . $f->id . '.php?action=index';
					 $page_text = $ca_span8 . ':: </span><a class="rvts8 nodec" ' . $sub_url . '">' . $ca_lang_l['newsletter'] . '(' . $f->id . ')</a>';
					 $admin_text = '<span class="rvts8">[</span><a class="rvts12" ' . $sub_url . '">' . $ca_lang_l['edit'] . '</a><span class="rvts8">]</span>';
					 $row_data = array($page_text, $admin_text, '');

					 if ($f->counter_on) {
						  $row_data[] = '';
						  if ($rss_count == '1')
								$row_data[] = '';
						  $row_data[] = '';
					 }
					 $table_data[] = $row_data;
				}
		  }

		  $page_text = $admin_text = $prot_text = '';
		  if ($f->counter_on) {
				$d_st = $ca_span8 . "[</span><a class='rvts12' href='" . $ca_pref_dir . "centraladmin.php?process=index&stat=detailed" . $ca_l;
				$d_end = $ca_lang_l['details'] . "</a>" . $ca_span8 . "]</span>";
				$l = Formatter::GFS($counter_stat, '<loads>', '</loads>');
				$u = Formatter::GFS($counter_stat, '<unique>', '</unique>');
				$first = Formatter::GFS($counter_stat, '<first>', '</first>');
				$r = Formatter::GFS($counter_stat, '<returning>', '</returning>');

				$counter_text = $ca_span8 . $ca_lang_l['total pageloads'] . ": " . $l . "</span>&nbsp;&nbsp;" . ($l != '0' ? $d_st . "&amp;f=h'>" . $d_end : '')
						  . F_BR . $ca_span8 . $ca_lang_l['unique visitors'] . ": " . $u . "</span>&nbsp;&nbsp;" . ($u != '0' ? $d_st . "&amp;f=u'>" . $d_end : '')
						  . F_BR . $ca_span8 . $ca_lang_l['first time visitors'] . ": " . $first . "</span>&nbsp;&nbsp;" . ($first != '0' ? $d_st . "&amp;f=f'>" . $d_end : '')
						  . F_BR . $ca_span8 . $ca_lang_l['returning visitors'] . ": " . $r . "</span>&nbsp;&nbsp;" . ($r != '0' ? $d_st . "&amp;f=r'>" . $d_end : '');
		  }
		  $row_data = array('', $admin_text, $prot_text);
		  if ($f->counter_on) {
				$row_data[] = $counter_text;
				$row_data[] = '';
				if ($rss_count == '1')
					 $row_data[] = '';
		  }
		  $table_data[] = $row_data;
		  $output.=Builder::adminTable('', $cap_arrays, $table_data);
	 }
	 $output = Formatter::fmtAdminScreen($output, build_menu($menu_title));
	 print GT($output);
}

function detailed_stats($old, &$menu_title) {
	 global $f, $ca_lang_l, $template_in_root, $ca_l_amp, $counter_ds_db_fname;

	 $output = '';
	 $day = 86400;
	 $display = Formatter::GFS($f->ca_settings, '<display>', '</display>');
	 if ($display == '')
		  $display = '0';
	 $flt = (isset($_GET['f']) ? Formatter::stripTags($_GET['f']) : ($display == '0' ? 'u' : 'h'));
	 if ($flt == 'h' || $flt == 'hrss' || $flt == 'flrss')
		  $h_lbl = 'hits';
	 elseif ($flt == 'u')
		  $h_lbl = $ca_lang_l['unique visitors'];
	 elseif ($flt == 'f')
		  $h_lbl = $ca_lang_l['first time visitors'];
	 elseif ($flt == 'r')
		  $h_lbl = $ca_lang_l['returning visitors'];

	 $fmt_label = '<span class="a_chart_label">%s</span>';
	 $pg = (isset($_GET['pid'])) ? intval($_GET['pid']) : '';

	 $br_stat = $os_stat = $res_stat = array();
	 foreach ($f->browsers as $k => $v)
		  $br_stat[$k] = 0;
	 foreach ($f->os as $k => $v)
		  $os_stat[$k] = 0;
	 $query_st_time = Date::microtimeFloat();

	 $d = time();
	 $d+=$day;
	 $dd = getdate($d);
	 $now = mktime(0, 0, 0, $dd['mon'], $dd['mday'], $dd['year']);
	 $today = getdate($now - $day);
	 $year_stat = array_fill(0, 12, 0);
	 $last30_stat = array_fill(0, 30, 0); // V graphs
	 $last30_d = array();
	 $offset = $today['mday'] - 30;
	 $mon_caption = $f->month_names[$today['mon'] - 1];
	 if ($offset < 0) {
		  $days_in_prev_m = Date::daysInMonth($today['mon'] - 1, $today['year']);
		  for ($i = $days_in_prev_m - abs($offset) + 1; $i <= $days_in_prev_m; $i++)
				$last30_d[] = $i;
		  for ($i = 1; $i <= $today['mday']; $i++)
				$last30_d[] = $i;
		  $mon_caption = $f->month_names[(($today['mon'] - 2) == -1 ? 11 : $today['mon'] - 2)] . ' - ' . $f->month_names[$today['mon'] - 1];
	 } else
		  for ($i = $offset; $i <= $today['mday']; $i++)
				$last30_d[] = $i;
	 $month_offsets = array();
	 $month_offsets[12] = $now - ($today['mday'] * $day);
	 $month_ids[12] = $today['mon'];
	 $cc = 1;
	 for ($i = 11; $i > 0; $i--) {
		  $month_ids[$i] = (($today['mon'] - $cc) > 0) ? $today['mon'] - $cc : 12 + ($today['mon'] - $cc);
		  $mj = (($today['mon'] - $cc) > 0) ? $today['year'] : $today['year'] - 1;
		  $month_offsets[$i] = $month_offsets[$i + 1] - (Date::daysInMonth($month_ids[$i], $mj) * $day);
		  $cc++;
	 }

	 if (file_exists($counter_ds_db_fname) && (filesize($counter_ds_db_fname) > 0)) {
		  $fp = fopen($counter_ds_db_fname, 'r');
		  $php_start_line = fgetcsv($fp, $f->max_chars);
		  $query_st_time = Date::microtimeFloat();

		  $year_ago = $now - $day * 355;
		  $month_ago = $now - $day * 30;
		  while ($data = fgetcsv($fp, $f->max_chars, '|')) {
				if (($pg == '' || $data[0] == $pg) && (($flt == 'h') || (isset($data[8]) && $flt == $data[8]) || ($flt == 'u' && isset($data[8]) && in_array($data[8], array('r', 'f'))))) {
					 if ($data[1] > $year_ago) {
						  foreach ($month_offsets as $k => $v)
								if ($data[1] > $v) {
									 $year_stat[$k - 1]+=1;
									 break;
								}
						  if ($data[1] > $month_ago) {
								$br_stat[$data[4]]+=1;
								$os_stat[$data[5]]+=1;
								if (strpos($data[6], 'screen.width') != false)
									 $data[6] = '1024x768';
								$res_stat[$data[6]] = (isset($res_stat[$data[6]])) ? $res_stat[$data[6]] + 1 : 1;
								$dday = (int) floor(($data[1] - $month_ago) / $day);
								$last30_stat[$dday]+=1;
						  }
					 }
				}
		  }
		  fclose($fp);
	 }

	 if (isset($_GET['pid'])) {
		  if ($template_in_root)
				$purl = str_replace('../', '', $_GET['purl']);
		  else
				$purl = (strpos($_GET['purl'], '../') === false) ? '../' . $_GET['purl'] : $_GET['purl'];
		  $menu_title.=(isset($_GET['pid']) ? ' <a target="_blank" href="' . $_GET['purl'] . '" title="' . $purl . '">"' . $_GET['pname'] . '"</a> ' . $ca_lang_l['page'] : '');
	 }
	 else {
		  $flt = $_GET['f'];
		  if ($flt == 'h')
				$menu_title.=$ca_lang_l['total pageloads'];
		  else if ($flt == 'u')
				$menu_title.=$ca_lang_l['unique visitors'];
		  else if ($flt == 'f')
				$menu_title.=$ca_lang_l['first time visitors'];
		  else if ($flt == 'r')
				$menu_title.=$ca_lang_l['returning visitors'];
	 }
	 //graphs
	 $output.='<table class="atable graphs ' . $f->atbg_class . '" cellpadding="3" cellspacing="0" style="margin:0 auto"><tr style="vertical-align:bottom;">';

	 $gr = array();
	 $labels = array();
	 $tot = 0;
	 foreach ($last30_stat as $k => $v) {
		  $gr[$k + 1] = $v;
		  $labels[$k] = $last30_d[$k];
		  $tot+=$v;
	 }
	 $output.='<tr><td class="' . $f->atbg_class . '" colspan="2" style="padding-bottom:10px;vertical-align:bottom;text-align:left;">' . sprintf($fmt_label, $ca_lang_l['last 30'] . ' ' . $mon_caption . ' (' . $tot . ' ' . $h_lbl . ')')
				. F_BR . F_BR . F_BR . Builder::vChart($gr, 570, 250, $labels, 0) . '</td></tr>';

	 $gr = array();
	 $labels = array();
	 $tot = 0;
	 foreach ($year_stat as $k => $v) {
		  $gr[$f->month_names[$month_ids[$k + 1] - 1]] = $v;
		  $labels[$k] = substr($f->month_names[$month_ids[$k + 1] - 1], 0, 3);
		  $tot+=$v;
	 }
	 $output.='<td class="' . $f->atbg_class . '" colspan="2" style="padding-bottom:10px;text-align:left;">' . sprintf($fmt_label, $ca_lang_l['last year'] . ' '
						  . ($dd['mon'] != 12 ? ($today['year'] - 1) . ' - ' : '') . $today['year'] . ' (' . $tot . ' ' . $h_lbl . ')') . F_BR . F_BR . F_BR . Builder::vChart($gr, 570, 200, $labels, 1) . '</td>';

	 $md = max($br_stat) / 50;
	 $other = 0;
	 $gr = array();
	 foreach ($br_stat as $k => $v) {
		  if ($v < $md)
				$other+=$v;
		  else
				$gr[$f->browsers[$k]] = $v;
	 }
	 $gr['other'] = $other;
	 $output.='<tr><td class="' . $f->atbg_class . '" style="width:auto;text-align:left;">' . sprintf($fmt_label, $ca_lang_l['browser']) . F_BR . F_BR . Builder::hChart($gr, 250, count($gr) * 15) . '</td>';

	 $md = (!empty($res_stat)) ? max($res_stat) / 100 : 0;
	 $other = 0;
	 $gr = array();
	 foreach ($res_stat as $k => $v) {
		  if ($v < $md)
				$other+=$v;
		  else
				$gr[$k] = $v;
	 }
	 $gr['other'] = $other;
	 $output.='<td class="' . $f->atbg_class . '" style="width:auto;text-align:left" rowspan="2">' . sprintf($fmt_label, $ca_lang_l['resolution']) . F_BR . F_BR . Builder::hChart($gr, 260, count($gr) * 15, 110) . '</td></tr><tr>';

	 $md = max($os_stat) / 200;
	 $other = 0;
	 $gr = array();
	 foreach ($os_stat as $k => $v) {
		  if ($v < $md)
				$other+=$v;
		  else
				$gr[$f->os[$k]] = $v;
	 } //
	 $gr['other'] = $other;
	 $output.='<td class="' . $f->atbg_class . '" style="width:auto;text-align:left;">' . sprintf($fmt_label, $ca_lang_l['os']) . F_BR . F_BR . Builder::hChart($gr, 250, count($gr) * 15) . '</td></tr></table>';

	 $abs_url = Linker::buildSelfURL('centraladmin.php') . "?process=index&amp;stat=olddetailed" . $ca_l_amp . "&amp;f=" . $flt
				. (isset($_GET['pid']) ? "&amp;pid=" . intval($_GET['pid']) . "&purl=" . $purl . "&pname=" . Formatter::stripTags($_GET['pname']) : '');
	 $output.=F_BR . '<input type="button" value=" ' . $ca_lang_l['detailed stat'] . ' " onclick="document.location=\'' . $abs_url . '\'">';
	 $output = $output . F_BR . F_BR . CA::formatNotice(str_replace('***', round(Date::microtimeFloat() - $query_st_time, 4), $ca_lang_l['page generated']));
	 $output = $f->navlist . $output . $f->navend;
	 //end graphs

	 return $output;
}

function old_detailed_stats(&$menu_title) {
	 global $f, $ca_pref_dir, $ca_span8, $ca_lang_l, $ca_l_amp, $template_in_root, $counter_ds_db_fname;

	 $flt = (isset($_GET['f']) ? Formatter::stripTags($_GET['f']) : 'h');
	 if ($flt == 'h' || $flt == 'hrss' || $flt == 'flrss')
		  $h_lbl = 'hits';
	 elseif ($flt == 'u')
		  $h_lbl = $ca_lang_l['unique visitors'];
	 elseif ($flt == 'f')
		  $h_lbl = $ca_lang_l['first time visitors'];
	 elseif ($flt == 'r')
		  $h_lbl = $ca_lang_l['returning visitors'];

	 $output = '';
	 $pages_list = CA::getSitemap('../', false, true);

	 $records = array();
	 $screen = (isset($_GET['page']) ? intval($_GET['page']) : 1);
	 $p = (isset($_GET['pid'])) ? intval($_GET['pid']) : '';

	 $records_count = 0;
	 if (file_exists($counter_ds_db_fname) && (filesize($counter_ds_db_fname) > 0)) {
		  $fp = fopen($counter_ds_db_fname, 'r');
		  $php_start_line = fgetcsv($fp, $f->max_chars);

		  if ($p != '') {
				$pos = ftell($fp);
				$p_pos = array();
		  }
		  while ($data = fgetcsv($fp, $f->max_chars, '|')) {
				if (strpos($data[0], '<?' . 'php echo "hi"; exit; /*') === false) {
					 $ch = ((($flt == 'hrss' || $flt == 'flrss') && isset($data[8]) && $data[8] == $flt) || ($flt == 'h') || ($flt == 'u' && isset($data[8]) && in_array($data[8], array('r', 'f'))));
					 if ($p != '') {
						  if ($data[0] == $p && $ch) {
								$p_pos[] = $pos;
								$records_count++;
						  }
					 } elseif ($ch)
						  $records_count++;
					 if ($p != '')
						  $pos = ftell($fp);
				}
		  }
		  rewind($fp);
		  $offset = ($screen == 1) ? 0 : ($screen - 1) * Navigation::recordsPerPage();
		  $limit_rec_to = ($screen * Navigation::recordsPerPage() > $records_count) ? Navigation::recordsPerPage() - ($screen * Navigation::recordsPerPage() - $records_count) : Navigation::recordsPerPage();
		  $offset = $records_count - $offset - $limit_rec_to;
		  $line = 0;
		  if ($p == '') {
				while ($data = fgetcsv($fp, $f->max_chars, '|')) {
					 if (strpos($data[0], '<?' . 'php echo "hi"; exit; /*') === false) {
						  if (($line >= $offset) && ($line < $offset + $limit_rec_to))
								$records[] = $data;$line++;
						  if ($line > $offset + $limit_rec_to)
								break;
					 }
				}
		  }
		  else {
				$p_pos = array_slice($p_pos, $offset, $limit_rec_to);
				foreach ($p_pos as $k => $pos) {
					 fseek($fp, $pos);
					 $data = fgetcsv($fp, $f->max_chars, '|');
					 $records[] = $data;
				}
		  }
		  fclose($fp);
		  $records = array_reverse($records);
	 }
	 if (isset($_GET['pid'])) {
		  $get_purl = Formatter::stripTags($_GET['purl']);
		  if ($template_in_root)
				$purl = str_replace('../', '', $get_purl);
		  else
				$purl = (strpos($_GET['purl'], '../') === false) ? '../' . $get_purl : $get_purl;
	 }

	 $url_part = $ca_pref_dir . "centraladmin.php?process=index&amp;stat=olddetailed" . $ca_l_amp . "&amp;" . (isset($_GET['f']) ? "&amp;f=" . Formatter::stripTags($_GET['f']) : '') . (isset($_GET['pid']) ? "&amp;pid=" . intval($_GET['pid']) . "&purl=" . $purl . "&pname=" . Formatter::stripTags($_GET['pname']) : '');
	 $menu_title = ' - ' . $ca_lang_l['detailed stat'] . ' ' . (isset($_GET['pid']) ? ' <a target="_blank" href="' . $get_purl . '" title="' . $purl . '">' . Formatter::stripTags($_GET['pname']) . '</a> ' . $ca_lang_l['page'] . ($flt == 'hrss' ? ' RSS' : '') : '');

	 $nav = Navigation::pageCA($records_count, $url_part, 0, $screen);

	 $cap_arrays = array(
		  $ca_lang_l['date'], $ca_lang_l['browser'], $ca_lang_l['os'], $ca_lang_l['resolution'],
		  Formatter::strToUpper($ca_lang_l['ip']) . ' / ' . $ca_lang_l['host'], $ca_lang_l['referrer']);
	 if (!isset($_GET['pid']))
		  $cap_arrays[] = $ca_lang_l['page name'];

	 $table_data = array();
	 foreach ($records as $k => $v) {
		  $fixed_date = Date::tzone($v[1]);
		  $ref = isset($v[7]) ? $v[7] : 'NA';
		  $q = '';
		  if (strpos($ref, 'q=') !== false) {
				$q = Formatter::GFS($ref, 'q=', '&');
				if ($q != '')
					 $q = $ca_span8 . Formatter::GFS($ref, 'q=', '&') . '</span>' . F_BR;
		  }
		  if (strpos($ref, '.google') !== false) {
				$refl = 'Google Search';
				if ($q == '') {
					 if (strpos($ref, 'url=') !== false) {
						  $q = $ca_span8 . Formatter::GFS($ref, 'url=', '&') . '</span>' . F_BR;
						  $refl.=' (url)';
					 }
				}
		  } elseif (strpos($ref, 'search.yahoo') !== false) {
				$refl = 'Yahoo Search';
				$q = (strpos($ref, 'p=') !== false) ? $ca_span8 . Formatter::GFS($ref, 'p=', '&') . '</span>' . F_BR : '';
		  } elseif (strpos($ref, 'bing.') !== false)
				$refl = 'Bing Search';
		  elseif (strpos($ref, 'yandex.') !== false) {
				$refl = 'Yandex Search';
				$q = (strpos($ref, 'text=') !== false) ? $ca_span8 . Formatter::GFS($ref, 'text=', '&') . '</span>' . F_BR : '';
		  } elseif ($ref == '/')
				$refl = 'home';
		  else {
				$refa = pathinfo($ref);
				$refl = $refa['basename'] == '' ? $ref : $refa['basename'];
				$refl = Formatter::GFS($refl, '', '?');
		  }

		  $row_data = array($ca_span8 . date('j-M-y H:i:s', $fixed_date) . "</span>",
				$ca_span8 . $f->browsers[$v[4]] . "</span>", $ca_span8 . $f->os[$v[5]] . "</span>", $ca_span8 . $v[6] . "</span>",
				$ca_span8 . Builder::ipLocator($v[2]) . '</span>' . F_BR . $ca_span8 . $v[3] . '</span>',
				$ca_span8 . ($ref != 'NA' ? $q . '<a target="_blank" href="' . $ref . '" alt="">' . wordwrap($refl, 30, "<br />\n", true) . '</a>' : $ca_lang_l['na']) . '</span>');

		  if (!isset($_GET['pid'])) {
				if (isset($pages_list[$v[0]])) {
					 $p_data = $pages_list[$v[0]];
					 if ($template_in_root)
						  $p_url = str_replace('../', '', $p_data[1]);
					 else
						  $p_url = (strpos($p_data[1], '../') === false) ? '../' . $p_data[1] : $p_data[1];
					 $p_n = strpos($p_data[0], '#') !== false && strpos($p_data[0], '#') == 0 ? str_replace('#', '', $p_data[0]) : $p_data[0];
					 $row_data[] = '<a class="rvts12" href="' . $p_url . '" alt="' . $p_url . '" title="' . $p_url . '">' . $p_n . '</a>';
				} else
					 $row_data[] = '';
		  }
		  $table_data[] = $row_data;
	 }
	 $output.=Builder::adminTable($nav, $cap_arrays, $table_data);
	 return $output;
}

function get_loads($counter_stat, $page_id, $page_url, $page_title, $rss = false, $flflag = '') { // COUNTER get page loads
	 global $ca_lang_l;

	 $px = ($rss ? ($flflag ? 'flrss' : 'rss') : 'l');
	 $loads = intval(Formatter::GFS($counter_stat, '<' . $px . '_' . $page_id . '>', '</' . $px . '_' . $page_id . '>'));
	 $page_total = ($loads > 0) ? $loads : ((!$flflag) ? $ca_lang_l['na'] : '');
	 if ($page_total != '')
		  $page_total = '<span class="rvts8">' . $page_total . '</span>';

	 return $page_total;
}

function manage_users() {
	 global $user, $ca_pref, $ca_pref_dir, $ca_lang_l, $ca_access_type_ex, $f, $ca_span8, $ca_areaarray, $ca_l_amp;

	 $output = '';
	 $curr_page = (isset($_GET['page']) ? intval($_GET['page']) : 1);

	 $search_used = (isset($_GET['search_string']) && !empty($_GET['search_string']) ? true : false);
	 if ($search_used) {
		  $search_string = Formatter::strToLower(Formatter::stripTags(trim($_GET['search_string'])));
		  $all_users = $user->getAllUsers($ca_pref);
		  $users_array = array();
		  foreach ($all_users as $k => $v) {
				if (strpos(Formatter::strToLower(Formatter::sth(urldecode($v['username']))), $search_string) !== false || strpos(Formatter::strToLower(Formatter::sth(urldecode($v['first_name']))), $search_string) !== false || strpos(Formatter::strToLower(Formatter::sth(urldecode($v['email']))), $search_string) !== false || strpos(Formatter::strToLower(Formatter::sth(urldecode($v['surname']))), $search_string) !== false) {
					 $users_array[] = $v;
				}
		  }
	 } else {
		  $users = db_get_users();
		  $users_array = ($users != '') ? $user->formatUsers($users) : array();
	 }

	 $total_records = count($users_array);
	 if ($total_records > 1) {
		  foreach ($users_array as $key => $row)
				$name[$key] = $row['username'];
		  $name_lower = array_map('strtolower', $name);
		  array_multisort($name_lower, SORT_ASC, $users_array);
	 }
	 $users_array = array_slice($users_array, ($curr_page - 1) * Navigation::recordsPerPage(), Navigation::recordsPerPage());

	 $section_names_arr = $ca_areaarray;
	 $cap_arrays = array();
	 $table_data = array();

	 $base = Linker::buildSelfURL('centraladmin.php');
	 $nav = '<script type="text/javascript"> function showSearchResult(){'
				. ' var search=document.getElementsByName("search_string")[0].value;document.location="' . $base . '?process=manageusers&search_string="+search; } </script> ';
	 $nav.='<div>
	 <div style="float:left;">
		 <input type="button" value=" ' . $ca_lang_l['add user'] . ' " onclick="document.location=\'' . $base . '?process=processuser' . $ca_l_amp . '\'">
		 <input type="button" value=" ' . $ca_lang_l['unconfirmed users'] . ' " onclick="document.location=\'' . $base . '?process=pendingreg' . $ca_l_amp . '\'">
		 <input type="button" value=" ' . $ca_lang_l['export'] . ' " onclick="document.location=\'' . $base . '?process=export' . $ca_l_amp . '\'">
	 </div>
	 <div style="text-align:right;">
		 <input type="text" name="search_string" value="" maxlength="50">
		 <input  type="button" name="search" value="' . $ca_lang_l['search'] . '" onclick="showSearchResult();">
	 </div>
	 </div>
	 <div style="clear:both;"></div>';

	 $nav.=Navigation::pageCA($total_records, $ca_pref_dir . 'centraladmin.php?process=manageusers' . ($search_used ? '&amp;search_string=' . Formatter::stripTags(trim($_GET['search_string'])) : ''), 0, $curr_page);
	 if (!empty($users_array)) {
		  $cap_arrays = array($ca_lang_l['user'], $ca_lang_l['details'], $ca_lang_l['access to'], $ca_lang_l['status']);
		  $table_data = array();
		  $url = $ca_pref_dir . "centraladmin.php?process=processuser" . $ca_l_amp;
		  foreach ($users_array as $key => $value) {
				if (!empty($value)) {
					 $usr = $value['username'];
					 $usrid = $value['id'];
					 $user = '<span class="rvts8">' . $usr . '</span>';
					 $user.='<div id="editaccess_' . $usrid . '" style="padding-top:10px;display:none;">'
								. build_edit_user_form('editaccess', '', $usr, $usrid, $value) . '</div>'
								. '<div id="editdetails_' . $usrid . '" style="padding-top:10px;display:none;">'
								. build_edit_user_form('editdetails', '', $usr, $usrid, $value) . '</div>'
								. '<div id="editpass_' . $usrid . '" style="padding-top:10px;display:none;">'
								. build_edit_user_form('editpass', '', $usr, $usrid, $value) . '</div>';

					 $details = $ca_span8 . Formatter::strToUpper(str_replace('&quot;', '"', un_esc($value['first_name']))) . " " . Formatter::strToUpper(str_replace('&quot;', '"', un_esc($value['surname']))) . F_BR . un_esc($value['email']) . "</span>";

					 $sv_eac = 'sv(\'editaccess_' . $usrid . '\');';
					 $svc_eat = 'svc(\'editaccess_' . $usrid . '\');';
					 $sv_edet = 'sv(\'editdetails_' . $usrid . '\');';
					 $svc_edet = 'svc(\'editdetails_' . $usrid . '\');';
					 $sv_epas = 'sv(\'editpass_' . $usrid . '\');';
					 $svc_epas = 'svc(\'editpass_' . $usrid . '\');';

					 $access = '';
					 $range = false;
					 if (!isset($value['access']))
						  $access = '<span class="rvts8">' . ($v['type'] == '0' ? $ca_lang_l['view all'] : $ca_lang_l['edit all']) . '</span>';
					 else {
						  foreach ($value['access'] as $k => $v) { //ALL-write
								if ($v['section'] == 'ALL') {
									 $access.='<span class="rvts8">' . ($v['type'] == '0' ? $ca_lang_l['view all'] : $ca_lang_l['edit all']) . '</span>';
								} else {
									 $sv_chr = 'sv(\'check_range_' . $usrid . '_' . $v['section'] . '\');';
									 $svc_chr = 'svc(\'check_range_' . $usrid . '_' . $v['section'] . '\');';
									 $section_name = (isset($section_names_arr[$v['section']]) ? $section_names_arr[$v['section']] : '');
									 if (empty($section_name))
										  $section_name = $v['section'];
									 $href = 'javascript:void(0);" onclick="' . $sv_chr . $svc_eat . $svc_edet . $svc_epas;
									 if ($f->use_prot_areas)
										  $access.='<span class="rvts8">' . $section_name . ' (' . $ca_access_type_ex[$v['type']] . ')</span>';
									 else
										  $access.='<span class="rvts8">' . $ca_access_type_ex['2'] . ' </span>';
									 $access.='<div id="check_range_' . $usrid . '_' . $v['section'] . '" style="padding-top:10px;display:none;">' . check_section_range(1, ($f->use_prot_areas ? $v['section'] : ''), $usr, $value) . ' </div> <span class="rvts8">[</span><a class="rvts12" href="' . $href . '">' . $ca_lang_l['check range'] . '</a><span class="rvts8">]</span> ' . F_BR;
									 $range = true;
								}
								if (!$f->use_prot_areas)
									 break;
						  }
					 }
					 $user_nav = array($ca_lang_l['edit access'] => 'javascript:void(0);" onclick="' . $sv_eac . $svc_edet . $svc_epas . ($range ? $svc_chr : ''),
						  $ca_lang_l['details'] => 'javascript:void(0);" onclick="' . $svc_eat . $sv_edet . $svc_epas . ($range ? $svc_chr : ''),
						  $ca_lang_l['password'] => 'javascript:void(0);" onclick="' . $svc_eat . $svc_edet . $sv_epas . ($range ? $svc_chr : ''),
						  $ca_lang_l['remove'] => $url . "&amp;removeuser=" . $usrid . '" onclick="javascript:return confirm(\'' . $ca_lang_l['remove MSG'] . '\')');

					 if ($value['status'] == '1') {
						  $status_value = $ca_lang_l['active'];
						  $status_link_label = $ca_lang_l['block'];
						  $act = 'block';
					 } else {
						  $status_value = $ca_lang_l['blocked'];
						  $status_link_label = $ca_lang_l['activate'];
						  $act = 'activate';
					 }
					 $status = '<span class="rvts8">' . $status_value . '</span>';
					 $status_nav = array($status_link_label => $url . "&amp;" . $act . "=" . $usrid);

					 $row_data = array(array($user, $user_nav), $details, ($access == '' ? '<span class="rvts8">No access</span>' : $access), array($status, $status_nav));
					 $table_data[] = $row_data;
				}
		  }
		  $output.=Builder::adminTable($nav, $cap_arrays, $table_data);
	 } else {
		  $table_data[] = array($ca_span8 . $ca_lang_l['none users'] . '</span>');
		  $output.=Builder::adminTable($nav, array(), $table_data);
	 }
	 $output = Formatter::fmtAdminScreen($output, build_menu());
	 print GT($output);
}

function process_users() {  //process add/edit/remove user
	 global $user, $ca_pref, $ca_lang_l, $ca_site_url, $f, $ca_areaarray, $ca_allunamechars;

	 $output = '';
	 $sections = '';
	 $details = '';
	 $news = '';

	 if (isset($_POST["select_all"]) && $_POST["select_all"] == 'no') {
		  $user_id = (isset($_POST["id"])) ? '_' . Formatter::stripTags($_POST["id"]) : '';
		  if ($f->use_prot_areas) {
				$a_k = 0;
				foreach ($ca_areaarray as $sec_id => $v) {	// to each section from ca_areaarray --> access_type assigned
					 $a_type = (isset($_POST["access_type" . $sec_id . $user_id]) ? Formatter::stripTags($_POST["access_type" . $sec_id . $user_id]) : '');
					 if ($a_type != '-1') {
						  $sections.='<access id="' . ($a_k + 1) . '" section="' . $sec_id . '" type="' . $a_type . '">';
						  if ($a_type == '2') {
								$section_range = get_prot_pages_list($sec_id, true);
								foreach ($section_range as $key => $val) {
									 $pid = $val['id'];
									 if (isset($_POST["access_to_page" . $pid]))
										  $sections.='<p id="' . ($key + 1) . '" page="' . $pid . '" type="' . Formatter::stripTags($_POST["access_to_page" . $pid]) . '">';
								}
						  }
						  $sections.='</access>';
						  $a_k++;
					 }
				}
		  }
		  else {
				$section_range = get_prot_pages_list('', true);
				$sections.='<access id="1" section="0" type="2">';
				foreach ($section_range as $key => $val) {
					 $pid = $val['id'];
					 if (isset($_POST["access_to_page" . $pid]))
						  $sections.='<p id="' . ($key + 1) . '" page="' . $pid . '" type="' . Formatter::stripTags($_POST["access_to_page" . $pid]) . '">';
				}
				$sections.='</access>';
		  }
	 }
	 elseif (isset($_POST["select_all"]) && $_POST["select_all"] == 'yesw') {
		  $sections.='<access id="1" section="ALL" type="1"></access>';
	 } //ALL-write
	 else {
		  $sections.='<access id="1" section="ALL" type="0"></access>';
	 } //ALL-read

	 if (isset($_POST["email"]) || isset($_POST["name"]) || isset($_POST["sirname"])) //details
		  $details.='<details email="' . Formatter::stripTags($_POST["email"]) . '" name="' . esc($_POST["name"]) . '" sirname="' . esc($_POST["sirname"]) . '"';
	 else
		  $details.='<details email="" name="" sirname=""';
	 $details.=(isset($_POST["creation_date"])) ? ' date="' . $_POST["creation_date"] . '"' : ' date="' . time() . '"'
				. (isset($_POST["sr"]) ? ' sr="' . $_POST["sr"] . '"' : ' sr="0"')
				. (isset($_POST["status"]) ? ' status="' . $_POST["status"] . '"' : ' status="1"') . '></details>';

	 if (isset($_POST["news_for"])) { //news - event manager
		  foreach ($_POST["news_for"] as $k => $v) {
				if (strpos($v, '%') !== false) {
					 list($p, $c) = explode('%', $v);
				} else {
					 $p = $v;
					 $c = '';
				}
				$news.='<news id="' . ($k + 1) . '" page="' . $p . '" cat="' . $c . '"></news>';
		  }
	 }

	 $flag = (isset($_POST['flag']) ? $_POST['flag'] : ''); //action flag - add, edit...

	 if (isset($_GET['search_string'])) {
		  manage_users();
		  exit;
	 } elseif (isset($_POST['save'])) {
		  $usrid = (isset($_POST["id"])) ? $_POST["id"] : 0;
		  $username = (isset($_POST['username']) ? $_POST['username'] : '');
		  $msg = '';

		  if ($flag == 'add' && !preg_match($ca_allunamechars, $_POST['username']))
				$msg = $ca_lang_l['can contain only'];
		  elseif ($flag == 'add' || $flag == 'editdetails') {
				if (empty($_POST['username']))
					 $msg = $ca_lang_l['fill in'] . ' ' . $ca_lang_l['username'];
				elseif ($_POST['username'] != $_POST['old_username'] && duplicated_user($_POST['username']))
					 $msg = $ca_lang_l['username exists'];
				elseif (!empty($_POST["email"]) && !Validator::validateEmail($_POST["email"]))
					 $msg = $ca_lang_l['nonvalid email'];
		  }
		  elseif ($flag == 'add' || $flag == 'editpass') {
				if (empty($_POST['password']))
					 $msg = $ca_lang_l['fill in'] . ' ' . $ca_lang_l['password'];
				elseif (empty($_POST['repeatedpassword']))
					 $msg = $ca_lang_l['repeat password'];
				elseif ($_POST['password'] != $_POST['repeatedpassword'])
					 $msg = $ca_lang_l['password and repeated password'];
				elseif (strlen(trim($_POST['password'])) < 5)
					 $msg = $ca_lang_l['your password should be'];
				elseif (strtolower($_POST['username']) == strtolower($_POST['password']))
					 $msg = $ca_lang_l['username equal password'];
		  }

		  if ($msg != '') {
				$msg = sprintf('<span class="rvts8"><em style="color:red;">%s</em></span>', $msg);
				if ($flag == 'add')
					 $output.=build_add_user_form($msg);
				else
					 $output.=build_edit_user_form($flag, $msg, $username);
		  }
		  else {
				if ($flag == 'add')
					 db_write_user('add', $usrid, $username, crypt($_POST['password']), $sections, $details, $news); // ADD USER
				elseif ($flag == 'editpass')
					 db_write_user('editpass', $usrid, $username, crypt($_POST['password'])); // CHANGE PASS
				elseif ($flag == 'editaccess')
					 db_write_user('editaccess', $usrid, $username, '', $sections); // CHANGE ACCESS
				elseif ($flag == 'editdetails')
					 db_write_user('editdetails', $usrid, $_POST['old_username'], '', '', $details, $news); // CHANGE DETAILS
				manage_users();
				exit;
		  }
	 }
	 elseif (isset($_GET['removeuser'])) { // REMOVE USER
		  $username_id = $_GET['removeuser'];
		  db_remove_user($username_id);
		  manage_users();
		  exit;
	 } elseif (isset($_GET['activate']) || isset($_GET['block'])) { // CHANGE STATUS
		  $usrid = (isset($_GET['activate'])) ? $_GET['activate'] : $_GET['block'];
		  db_write_user((isset($_GET['activate'])) ? 'activate' : 'block', $usrid);

		  $user_data = $user->getUser($usrid, $ca_pref, '', $usrid);
		  if (!empty($user_data['email'])) {
				$content = (isset($_GET['activate'])) ? $ca_lang_l['sr_activated_msg'] : $ca_lang_l['sr_blocked_msg'];
				$subject = (isset($_GET['activate'])) ? $ca_lang_l['sr_activated_subject'] : $ca_lang_l['sr_blocked_subject'];
				$content = str_replace(array('%%username%%', '%%USERNAME%%', '%%site%%'), array($user_data['username'], $user_data['username'], $ca_site_url), $content);
				$subject = str_replace('%%site%%', $ca_site_url, $subject);
				MailHandler::sendMailCA(str_replace("##", '<br>', $content), $subject, $user_data['email']);
		  }
		  manage_users();
		  exit;
	 } else
		  $output.=build_add_user_form();

	 $output = Formatter::fmtAdminScreen($output, build_menu($flag == 'add' ? ' - ' . $ca_lang_l['add user'] : ''));
	 $output = GT($output);
	 print $output;
}

function check_section_range($standalone, $section_id = '', $username = '', $user_data = '') { // check section range screen
	 global $template_in_root, $ca_lang_l, $f, $ca_access_type_ex, $ca_access_type, $ca_areaarray;

	 $section_range = get_prot_pages_list($section_id, true);
	 $access_by_page = array();
	 $section_name = (isset($ca_areaarray[$section_id]) ? $ca_areaarray[$section_id] : '');
	 if ($username != '') {
		  if (!empty($user_data)) {
				foreach ($user_data['access'] as $k => $v) {
					 if ($v['section'] == $section_id || $section_id == '') {
						  if ($v['type'] == '2') {
								$page_access = $v['page_access'];
								break;
						  } else {
								$a_type = $v['type'];
						  } if ($section_id != '')
								break;
					 }

					 if ($section_id == '' && !$f->use_prot_areas && $v['type'] != '2') {
						  $sec_r = get_prot_pages_list($v['section'], true);
						  foreach ($sec_r as $vv)
								$access_by_page[$vv['id']] = $v['type'];
					 }
				}
		  }
		  if (isset($page_access))
				foreach ($page_access as $k => $v) {
					 $access_by_page[$v['page']] = $v['type'];
				}
	 }

	 $legend = sprintf('<span class="rvts8">%s</span>', ($standalone) ? $ca_lang_l['section'] . ": " . $section_name : $ca_lang_l['page level']);
	 $pro = '';
	 $unpro = '';
	 $line = '<div style="position:relative;"><div style="padding-left:10px;min-height:20px;">:: <a class="rvts12" target="_blank" title="%s" href="%s">%s</a></div><div style="position:absolute;right:0px;width:120px;top:0px" align="right">%s</div></div>';

	 $output = '<div style="width:285px;"><div style="padding-left:15px;" align="left">';
	 foreach ($section_range as $k => $v) {
		  if ($template_in_root)
				$fixed_url = str_replace('../', '', $v['url']);
		  elseif (strpos($v['url'], '/') !== false)
				$fixed_url = $v['url'];
		  else
				$fixed_url = '../' . $v['url'];
		  $url = str_replace('..', '', $v['url']);

		  if ($v['typeid'] == '136' || $v['typeid'] == '137' || $v['typeid'] == '143' || $v['typeid'] == '138') {
				if ($v['protected'] == 'TRUE')
					 $access_type_f = in_array($v['typeid'], $f->sp_pages_ids) ? array('0' => 'view', '1' => 'edit', '3' => 'edit own posts', '2' => 'no access') : array('0' => 'view', '2' => 'no access');
				else
					 $access_type_f = array('0' => 'no access', '1' => 'edit', '3' => 'edit own posts'); //edit own
		  }
		  else {
				if ($v['protected'] == 'TRUE')
					 $access_type_f = in_array($v['typeid'], $f->sp_pages_ids) ? array('0' => 'view', '1' => 'edit', '2' => 'no access') : array('0' => 'view', '2' => 'no access');
				else
					 $access_type_f = array('0' => 'no access', '1' => 'edit'); //edit own
		  }

		  if (!$standalone) {
				if (isset($access_by_page) && isset($access_by_page[$v['id']]))
					 $default = $access_by_page[$v['id']];
				else
					 $default = (!isset($page_access)) ? '2' : ($v['protected'] == 'TRUE' ? '2' : '0');
				$combo = Builder::buildSelect('access_to_page' . $v['id'], $access_type_f, $default, 'style="width: 110px"');
		  }
		  elseif (isset($access_by_page)) {
				$combo = '<span class="rvts8">[ ' . (isset($access_by_page[$v['id']]) && isset($access_type_f[$access_by_page[$v['id']]]) ? $access_type_f[$access_by_page[$v['id']]] : 'no access') . ' ]</span>';
		  } else
				$combo = '<span class="rvts8">[ ' . (isset($a_type) ? $ca_access_type[$a_type] : $ca_access_type_ex['2']) . ' ]</span>';

		  if ($v['protected'] == 'TRUE')
				$pro.=sprintf($line, $url, $fixed_url, $v['name'], $combo);
		  elseif ($v['protected'] == 'FALSE')
				$unpro.=sprintf($line, $url, $fixed_url, $v['name'], $combo);
	 }
	 $pro_label = ($pro != '') ? F_BR . $ca_lang_l['protected pages'] : '';
	 $unpro_label = ($unpro != '') ? $ca_lang_l['unprotected pages'] : '';
	 if ($f->use_prot_areas) {
		  $line = '<fieldset style="padding:3px;"><legend>%s</legend><span class="rvts8">%s</span>' . F_BR . "%s" . F_BR . '<span class="rvts8">%s</span>' . F_BR . '%s</fieldset>';
		  $output.=sprintf($line, $legend, $pro_label, $pro, $unpro_label, $unpro);
	 } else
		  $output.=sprintf('<span class="rvts8">%s</span>' . F_BR . "%s" . F_BR . '<span class="rvts8">%s</span>' . F_BR . '%s', $pro_label, $pro, $unpro_label, $unpro);

	 return $output . '</div></div>';
}

function check_pending_users($msg = '') {
	 global $user, $ca_pref_dir, $ca_lang_l, $ca_l, $ca_l_amp, $ca_span8, $ca_access_type_ex, $ca_site_url, $ca_areaarray;

	 if (isset($_GET['removeuser'])) {	// REMOVE USER
		  $user_id = $_GET['removeuser'];
		  db_remove_user($user_id, 'selfreg_users');
		  $msg = F_BR . $ca_lang_l['user removed'];
	 }
	 $users = db_get_users('selfreg_users');
	 $users_array = ($users != '') ? $user->formatUsers($users) : array();

	 if (isset($_GET['resend'])) {	// RE_SEND CONFIRMATION EMAIL TO USER
		  $user_id = $_GET['resend'];
		  foreach ($users_array as $k => $v) {
				if ($v['id'] == $user_id) {
					 $user_info = $v;
					 break;
				}
		  }

		  $link = Linker::buildSelfURL('centraladmin.php') . '?id=' . $user_id . '&process=register' . $ca_l;
		  $message = str_replace(array("%CONFIRMLINK%", '%%USERNAME%%'), array('%confirmlink%', '%%username%%'), $ca_lang_l['sr_email_msg']);
		  $content = str_replace(array("##", '%confirmlink%', '%%username%%', '%%site%%'), array('<br>', '<a href="' . $link . '">' . $link . '</a>', $user_info['username'], $ca_site_url), $message);
		  $subject = str_replace('%%site%%', $ca_site_url, $ca_lang_l['sr_email_subject']);
		  $send_to_email = $user_info["email"];
		  $log_data = 'USER:' . $user_info['username'] . ' EMAIL:' . $user_info["email"];
		  $log_msg = 'success';

		  $result = MailHandler::sendMailCA($content, $subject, $send_to_email);
		  if ($result == "1") {
				$log_msg.=", email SENT";
				$msg = F_BR . $ca_lang_l['email resent'] . ' ' . Formatter::strToUpper($user_info['username']);
		  } else {
				$log_msg = 'fail, email FAILED (' . Formatter::stripTags($result) . ')';
				$msg = 'Email FAILED. Try again.';
		  }
		  write_log('resend', $log_data, $log_msg);
	 }

	 $output = ($msg != '' ? '<span class="rvts8">' . $msg . '</span>' . F_BR . F_BR : '');
	 if (!empty($users_array)) {
		  $cap_arrays = array($ca_lang_l['user'], $ca_lang_l['details'], $ca_lang_l['access to']);
		  $table_data = array();
		  $url = $ca_pref_dir . "centraladmin.php?process=";
		  foreach ($users_array as $value) {
				if (!empty($value)) {
					 $usr = $value['username'];
					 $user = '<span class="rvts8">' . $usr . '</span>';
					 $user_nav = array($ca_lang_l['confirm'] => $url . "register&amp;id=" . $value['id'] . "&amp;flag=admin" . $ca_l_amp,
						  $ca_lang_l['resend'] => $url . "pendingreg&amp;resend=" . $value['id'] . $ca_l_amp . '" onclick="javascript:return confirm(\'' . $ca_lang_l['resend MSG'] . ' '
						  . Formatter::strToUpper($usr) . " - " . un_esc($value['first_name']) . " " . un_esc($value['surname']) . '?\')', $ca_lang_l['remove'] => $url . "pendingreg&amp;removeuser=" . $value['id'] . $ca_l_amp
						  . '" onclick="javascript:return confirm(\'' . $ca_lang_l['remove MSG'] . '\')');
					 $details = $ca_span8 . Formatter::strToUpper(un_esc($value['first_name'])) . " " . Formatter::strToUpper(un_esc($value['surname'])) . F_BR . $value['email'] . "</span>";

					 $access = '';
					 $access = '<span class="rvts8">';
					 if (!isset($value['access']))
						  $access.=$ca_lang_l['view all'] . '</span>';
					 else {
						  foreach ($value['access'] as $k => $v) { //ALL-write
								if ($v['section'] == 'ALL')
									 $access.=($v['type'] == '0' ? $ca_lang_l['view all'] : $ca_lang_l['edit all']) . '</span>';
								else {
									 $section_name = $ca_areaarray[$v['section']];
									 if (empty($section_name))
										  $section_name = $v['section'];
									 $access.=$section_name . ' (' . $ca_access_type_ex[$v['type']] . ')</span>';
								}
						  }
					 }
					 $row_data = array(array($user, $user_nav), $details, $access);
					 $table_data[] = $row_data;
				}
		  }
		  $output.=Builder::adminTable('', $cap_arrays, $table_data);
	 }
	 else {
		  $table_data[] = array($ca_span8 . $ca_lang_l['none users'] . '</span>');
		  $output.=Builder::adminTable('', array(), $table_data);
	 }
	 $output = Formatter::fmtAdminScreen($output, build_menu());
	 print GT($output);
}

function conf_counter() {
	 global $f, $ca_db_settings_file, $ca_pref_dir, $ca_lang_l, $ca_l_amp, $template_in_root, $ca_template_file;
	 $C_UNIQUE_START_COUNT = 0;
	 $C_LOADS_START_COUNT = 0;
	 $C_GRAPHICAL = 1;
	 $C_MAX_VISIT_LENGHT = 1800;
	 $C_NUMBER_OF_DIGITS = 8;
	 $C_DISPLAY = 0;	//1- page loads; 0- unique

	 $visit_len_list = array('1800' => '30 min', '3600' => '1 h', '7200' => '2 h', '10800' => '3 h', '216000' => '6 h', '432000' => '12 h', '864000' => '24 h');
	 $number_digits_list = array(4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10);
	 $show_list = array('show unique visitors', 'show pageloads');
	 $counter_type = array('text', 'graphical');
	 $table_data = array();
	 $end = '';
	 if (!isset($_POST['save'])) {
		  $settings = Formatter::GFS($f->ca_settings, '<counter>', '</counter>');
		  $max_visit_len = (strpos($settings, '<max_visit_len>') !== false) ? Formatter::GFS($settings, '<max_visit_len>', '</max_visit_len>') : $C_MAX_VISIT_LENGHT;
		  $number_of_digits = (strpos($settings, '<number_digits>') !== false) ? Formatter::GFS($settings, '<number_digits>', '</number_digits>') : $C_NUMBER_OF_DIGITS;
		  $size = (strpos($settings, '<size>') !== false) ? Formatter::GFS($settings, '<size>', '</size>') : 1;
		  $display = (strpos($settings, '<display>') !== false) ? Formatter::GFS($settings, '<display>', '</display>') : $C_DISPLAY;
		  $loads_start_count = (strpos($settings, '<loads_start_value>') !== false) ? Formatter::GFS($settings, '<loads_start_value>', '</loads_start_value>') : $C_LOADS_START_COUNT;
		  $unique_start_count = (strpos($settings, '<unique_start_value>') !== false) ? Formatter::GFS($settings, '<unique_start_value>', '</unique_start_value>') : $C_UNIQUE_START_COUNT;
		  $graphical = (strpos($settings, '<graphical>') !== false) ? Formatter::GFS($settings, '<graphical>', '</graphical>') : $C_GRAPHICAL;
		  $s = (isset($_GET['size']) ? $_GET['size'] : $size);
		  $rss_count = (strpos($settings, '<rss_count>') !== false) ? Formatter::GFS($settings, '<rss_count>', '</rss_count>') : '0';

		  $table_data[] = array($ca_lang_l['display'], Builder::buildSelect('display', $show_list, (isset($_GET['display']) ? $_GET['display'] : $display)));
		  $table_data[] = array($ca_lang_l['number of digits'], Builder::buildSelect('number_digits', $number_digits_list, (isset($_GET['num_digits']) ? $_GET['num_digits'] : $number_of_digits - 1)));
		  $table_data[] = array($ca_lang_l['maximum visit length'], Builder::buildSelect('max_visit_len', $visit_len_list, (isset($_GET['v_length']) ? $_GET['v_length'] : $max_visit_len)));
		  $table_data[] = array($ca_lang_l['unique start offset'], Builder::buildInput('u_st_count', (isset($_GET['u_offset']) ? $_GET['u_offset'] : $unique_start_count), '', '', 'text', 'size="10"'));
		  $table_data[] = array($ca_lang_l['pageloads start offset'], Builder::buildInput('l_st_count', (isset($_GET['l_offset']) ? $_GET['l_offset'] : $loads_start_count), '', '', 'text', 'size="10"'));
		  $table_data[] = array($ca_lang_l['counter type'], Builder::buildSelect('graphical', $counter_type, (isset($_GET['graphical']) ? $_GET['graphical'] : $graphical)));

		  $counter_type = '';
		  $inp = '<div style="text-align:left;height:25px;padding-left:10px;"><input type="radio" name="size" value="%s" %s><img style="position:absolute;" src="' . ($template_in_root ? '' : '../') . 'ezg_data/c%s.gif" alt=""></div>';
		  $cnt = count($f->counter_images) + 1;
		  for ($i = 1; $i < $cnt; $i++)
				$counter_type.=sprintf($inp, $i, ($s == $i) ? 'checked="checked"' : '', $i);
		  $table_data[] = array('', $counter_type);

		  $rss_count_line = '<input type="checkbox" name="rss_count" value="1"' . ($rss_count == '1' ? ' checked="checked"' : '')
					 . '> <span class="rvts8">' . $ca_lang_l['count when RSS feed'] . "</span> ";
		  $table_data[] = array('', $rss_count_line);

		  $end = ca_getformbuttons();
		  $end.=F_BR . '<div style="text-align: right"><input type="button" value=" ' . $ca_lang_l['reset counter'] . ' " onclick="document.location=\'' . Linker::buildSelfURL('centraladmin.php') . '?process=resetcounter' . $ca_l_amp . '\'"></div>';
	 } else {
		  $newsettings = '<max_visit_len>' . $_POST['max_visit_len'] . '</max_visit_len><graphical>' . $_POST['graphical'] . '</graphical>'
					 . '<number_digits>' . ($_POST['number_digits'] + 1) . '</number_digits><size>' . $_POST['size'] . '</size><display>' . $_POST['display'] . '</display>'
					 . '<loads_start_value>' . $_POST['l_st_count'] . '</loads_start_value><unique_start_value>' . $_POST['u_st_count'] . '</unique_start_value><rss_count>' . (isset($_POST['rss_count']) ? $_POST['rss_count'] : 0) . '</rss_count>';
		  $re = File::writeTaggedData('counter', $newsettings, $ca_db_settings_file, $ca_template_file);
		  $table_data[] = array('', '<span class="rvts8">' . (($re == true) ? $ca_lang_l['settings saved'] : "Settings not saved. ERROR.") . '</span>');
	 }
	 $output = '<form name="frm" action="' . $ca_pref_dir . 'centraladmin.php?process=confcounter' . $ca_l_amp . '" method="post"><div style="text-align:left">';
	 $output.=Builder::addEntryTable($table_data, $end) . "</div></form>";
	 $output = Formatter::fmtAdminScreen($output, build_menu());
	 $output = GT($output);
	 print $output;
}

function conf_registration() {
	 global $ca_db_settings_file, $f, $ca_pref_dir, $ca_lang_l, $ca_l_amp, $ca_template_file, $ca_areaarray, $ca_access_type_ex;

	 $output = '';
	 $admin_email = '';
	 $terms_url = '';
	 $notes = '';
	 $access_str = '';
	 $access = array();
	 $confirm_message = '';
	 $input_size = 500;
	 $input = '<input class="input1" type="text" name="%s" value="%s" style="width:' . $input_size . 'px" maxlength="255">' . F_BR;
	 $table_data = array();
	 $end = '';
	 $abs_url = Linker::buildSelfURL('centraladmin.php');
	 if (!isset($_POST['save'])) {
		  $settings = Formatter::GFS($f->ca_settings, '<registration>', '</registration>');
		  if (strpos($settings, '<admin_email>') !== false)
				$admin_email = Formatter::GFS($settings, '<admin_email>', '</admin_email>');
		  if (strpos($settings, '<terms_url>') !== false)
				$terms_url = Formatter::GFS($settings, '<terms_url>', '</terms_url>');
		  if (strpos($settings, '<notes>') !== false)
				$notes = Formatter::GFS($settings, '<notes>', '</notes>');
		  if (strpos($settings, '<confirm_message>') !== false)
				$confirm_message = Formatter::GFS($settings, '<confirm_message>', '</confirm_message>');
		  if (strpos($settings, '<access>') !== false)
				$access_str = Formatter::GFS($settings, '<access>', '</access>');
		  $require_approval = Formatter::GFS($settings, '<require_approval>', '</require_approval>');
		  if ($require_approval == '')
				$require_approval = '0';
		  if ($access_str != '')
				$temp_access = explode('|', $access_str);
		  if (isset($temp_access)) {
				foreach ($temp_access as $k => $v) {
					 $t = explode('%%', $v);
					 $page_level_str = Formatter::GFS($v, '(', ')');
					 if (!empty($page_level_str))
						  $t[1] = str_replace('(' . $page_level_str . ')', '', $t[1]);
					 if ($t[1] == '2') {
						  $page_level_arr = explode(';', $page_level_str);
						  foreach ($page_level_arr as $vv) {
								$value = explode('%', $vv);
								$page_access_arr [] = array('page' => $value[0], 'type' => $value[1]);
						  }
						  $access[] = array('section' => $t[0], 'type' => $t[1], 'page_access' => $page_access_arr);
					 } else
						  $access[] = array('section' => $t[0], 'type' => $t[1]);
				}
		  }
		  $admin_email_value = (isset($_GET['admin_email']) ? $_GET['admin_email'] : $admin_email);

		  $admin_mail_line = sprintf($input, 'admin_email', $admin_email_value) . F_BR . CA::formatNotice($ca_lang_l['confreg_msg2']);
		  $table_data[] = array($ca_lang_l['admin email'] . $f->fmt_star, $admin_mail_line);

		  $terms_line = sprintf($input, 'terms_url', (isset($_GET['terms_url']) ? $_GET['terms_url'] : $terms_url)) . F_BR . CA::formatNotice($ca_lang_l['confreg_msg1']);
		  $table_data[] = array($ca_lang_l['terms url'], $terms_line);

		  $notes_line = '<textarea class="input1" name="notes" style="width:' . $input_size . 'px" cols="20" rows="5">' . (isset($_GET['notes']) ? $_GET['notes'] : $notes) . '</textarea>' . F_BR
					 . F_BR . CA::formatNotice($ca_lang_l['confreg_msg5']);
		  $table_data[] = array($ca_lang_l['notes'], $notes_line);

		  $checked_all_read = (empty($access) || $access[0]['section'] == 'ALL' && $access[0]['type'] == '0');
		  $checked_all_write = (!empty($access) && $access[0]['section'] == 'ALL' && $access[0]['type'] == '1');
		  $checked_selected = (!empty($access) && $access[0]['section'] != 'ALL');

		  $line_template = '<div style="width:260px;"><div style="position:relative;"><div style="padding-left:10px;min-height:20px;"><span class="rvts8">&nbsp;&nbsp;%s</span></div><div style="position:absolute;right:0px;width:120px;top:0px" align="right">%s</div></div></div>';

		  $confirm_line = '<textarea class="input1" name="confirm_message" style="width:' . $input_size . 'px" cols="20" rows="5">' . (isset($_GET['confirm_message']) ? $_GET['confirm_message'] : $confirm_message) . '</textarea>' . F_BR
					 . F_BR . CA::formatNotice($ca_lang_l['confreg_msg6']);
		  $table_data[] = array($ca_lang_l['confirm_message'], $confirm_line);

		  $access_line = '<input type="radio" id="select_all_read" name="select_all" value="yes" ' . ($checked_all_read ? 'checked="checked"' : '')
					 . ' onclick="javascript:hide_div(\'selected_holder\');"> <span class="rvts8">' . $ca_lang_l['view all'] . "</span>" . F_BR;
		  $access_line.='<input type="radio" id="select_all_edit" name="select_all" value="yesw" ' . ($checked_all_write ? 'checked="checked" ' : '')
					 . ' onclick="javascript:hide_div(\'selected_holder\');"> <span class="rvts8">' . $ca_lang_l['edit all'] . "</span>" . F_BR;
		  if (!empty($ca_areaarray)) {
				$access_line.='<input type="radio" id="select_all_sel" name="select_all" value="no" ' . (!empty($access) && $access[0]['section'] != 'ALL' ? 'checked="checked"' : '') . ' onclick="javascript:show_div(\'selected_holder\');">
				<span class="rvts8"> ' . $ca_lang_l['page level'] . ' </span>' . F_BR;
		  } else {
				$access_line.=F_BR . '<span class="rvts8">' . $ca_lang_l['adduser_msg1'] . '</span>';
		  }

		  $selected_sec_ids = array();
		  $selected_sec_access = array();
		  if ($access != '') {
				foreach ($access as $k => $v) {
					 $selected_sec_ids[] = $v['section'];
					 $selected_sec_access[] = $v['type'];
				}
		  }

		  $access_line.='<div id="selected_holder" style="display:' . ($checked_selected ? 'block' : 'none') . ';">';
		  if ($f->use_prot_areas) {
				foreach ($ca_areaarray as $k => $v) {
					 $cur_sec_id = $k;
					 $cur_sec_name = $v;
					 $secaccess_type = (!$checked_selected) ? '2' : '-1';
					 $index = array_search($cur_sec_id, $selected_sec_ids);
					 if ($index !== false)
						  $secaccess_type = $selected_sec_access[$index]['type'];

					 $access_line.=F_BR . sprintf($line_template, $cur_sec_name, Builder::buildSelect('access_type' . $cur_sec_id, $ca_access_type_ex, $secaccess_type, 'onchange="javascript:tS(\'' . $cur_sec_id . '\');"'));
					 $access_line.='<div id="section' . $cur_sec_id . '" style="display:' . (($secaccess_type == '2') ? "block" : "none") . '">';
					 $access_line.=check_section_range(0, $cur_sec_id, 'none', array('access' => $access)) . '</div>';
				}
		  } else
				$access_line.='<div>' . check_section_range(0, '', 'none', array('access' => $access)) . "</div>";

		  $access_line.='</div>' . F_BR . '<div>' . CA::formatNotice($ca_lang_l['confreg_msg7']) . F_BR . '<span class="rvts8"><b>' . $ca_lang_l['view'] . '</b></span><span class="rvts8"> - ' . $ca_lang_l['adduser_msg2'] . '</span>' . F_BR . '<span class="rvts8"><b>' . $ca_lang_l['edit'] . '</b></span><span class="rvts8"> - ' . $ca_lang_l['adduser_msg3'] . '</span></div>';

		  $table_data[] = array($ca_lang_l['access to'], $access_line);
		  $require_line = '<input type="checkbox" id="require_approval" name="require_approval" value="1"' . ($require_approval == '1' ? ' checked="checked"' : '') . ($checked_all_write ? ' disabled="disabled"' : '') . '> <span class="rvts8">' . $ca_lang_l['require_approval'] . "</span> ";
		  $table_data[] = array('', $require_line);
		  $end = ca_getformbuttons();
		  $end.=F_BR . "<script language=\"javascript\" type=\"text/javascript\">function tS(id){if(document.getElementById('access_type'+id).selectedIndex==2) document.getElementById('section'+id).style.display='block'; else document.getElementById('section'+id).style.display='none'; } function show_div(id){document.getElementById(id).style.display='block';} function hide_div(id){document.getElementById(id).style.display='none';}</script>";
	 }
	 else {
		  $require_app = (isset($_POST['require_approval']) ? $_POST['require_approval'] : '0');

		  $sections = array();
		  if (isset($_POST["select_all"]) && $_POST["select_all"] == 'no') {
				if ($f->use_prot_areas) {
					 foreach ($ca_areaarray as $sec_id => $v) {
						  $a_type = (isset($_POST["access_type" . $sec_id]) ? Formatter::stripTags($_POST["access_type" . $sec_id]) : '0');
						  if ($a_type == '2') {
								$page_access_arr = array();
								$section_range = get_prot_pages_list($sec_id, true);
								foreach ($section_range as $key => $val) {
									 $pid = $val['id'];
									 if (isset($_POST["access_to_page" . $pid])) {
										  $page_access_arr[] = $pid . '%' . Formatter::stripTags($_POST["access_to_page" . $pid]);
										  if ($_POST["access_to_page" . $pid] == '1' || $_POST["access_to_page" . $pid] == '3')
												$require_app = '1';
									 }
								}
								if (!empty($page_access_arr))
									 $page_access_str = implode(';', $page_access_arr);
								$sections[] = $sec_id . '%%' . $a_type . (!empty($page_access_str) ? '(' . $page_access_str . ')' : '');
						  }
						  elseif ($a_type != '-1') {
								$sections[] = $sec_id . '%%' . $a_type;
								if ($a_type == '1' || $a_type == '3')
									 $require_app = '1';
						  }
					 }
				}
				else {
					 $page_access_arr = array();
					 $section_range = get_prot_pages_list('', true);
					 foreach ($section_range as $key => $val) {
						  $pid = $val['id'];
						  if (isset($_POST["access_to_page" . $pid]))
								$page_access_arr[] = $pid . '%' . Formatter::stripTags($_POST["access_to_page" . $pid]);
					 }
					 if (!empty($page_access_arr))
						  $page_access_str = implode(';', $page_access_arr);
					 $sections[] = '0%%2' . (!empty($page_access_str) ? '(' . $page_access_str . ')' : '');
				}
		  }
		  elseif (isset($_POST["select_all"]) && $_POST["select_all"] == 'yesw') {
				$sections [] = "ALL%%1";
				$require_app = '1';
		  } //ALL-write
		  else {
				$sections[] = "ALL%%0";
		  } //ALL-read

		  $newsettings = '<admin_email>' . $_POST['admin_email'] . '</admin_email><terms_url>' . $_POST['terms_url'] . '</terms_url>' . '<notes>' . $_POST['notes'] . '</notes>' . '<confirm_message>' . $_POST['confirm_message'] . '</confirm_message>'
					 . '<require_approval>' . $require_app . '</require_approval>' . '<access>' . implode('|', $sections) . '</access>';

		  $re = File::writeTaggedData('registration', $newsettings, $ca_db_settings_file, $ca_template_file);
		  $table_data[] = array('', '<span class="rvts8">' . (($re == true) ? $ca_lang_l['settings saved'] : "Settings not saved. ERROR.") . '</span>');
	 }
	 $output = $f->navtop . '<input type="button" value=" ' . $ca_lang_l['settings'] . ' " onclick="document.location=\'' . $abs_url . '?process=confreg\'"> <input type="button" value=" ' . $ca_lang_l['language'] . ' " onclick="document.location=\'' . $abs_url . '?process=confreglang\'">' . $f->navend . F_BR;
	 $output.='<form name="frm" action="' . $ca_pref_dir . 'centraladmin.php?process=confreg' . $ca_l_amp . '" method="post">';
	 $output.='<div style="text-align:left">' . Builder::addEntryTable($table_data, $end) . "</div></form>";
	 $output = Formatter::fmtAdminScreen($output, build_menu(' - ' . $ca_lang_l['settings']));
	 $output = GT($output);
	 if (!isset($_POST['save'])) {
		  $mm = '$(document).ready(function(){ $("#select_all_edit").click(function() {$("#require_approval").attr({checked:true, disabled:true}); });'
					 . ' $("#select_all_read").click(function() {$("#require_approval").attr({checked:false, disabled:false}); });'
					 . ' $("#select_all_sel").click(function() {$("#require_approval").attr({checked:false, disabled:false}); });'
					 . ' $("select").change(function() { option_val=this.options[this.selectedIndex].value; '
					 . ' if((option_val==1) || (option_val==3)) {$("#require_approval").attr({checked:true,disabled:true});} '
					 . ' else if((option_val==0) || (option_val==2)) {$("#require_approval").attr({checked:false,disabled:false}); $("select").each(function() { v=$(this).val(); if((v==1) || (v==3)) {$("#require_approval").attr({checked:true,disabled:true}); } }); } ' . '});'
					 . '});';
		  $output = Builder::includeScript($mm, $output);
	 }
	 print $output;
}

# ----------------- build HTML functions

function GT($html_output, $include_counter_flag = false, $title = '', $lang_templ_page = false) {
	 global $ca_template_file, $ca_lang_l, $f, $template_in_root, $ca_action_id, $ca_sitemap_arr, $ca_lang_template, $ca_user_actions;

	 if ($lang_templ_page && $ca_lang_template != '') {
		  $ca_template = $ca_lang_template;
		  $templ_root = (strpos($ca_template, '/') === false);
		  if ($templ_root)
				$ca_template = '../' . $ca_template;
	 }
	 else {
		  $ca_template = $ca_template_file;
		  $templ_root = $template_in_root;
	 }

	 if (strpos($ca_template, 'template_source') !== false) {
		  $dm = '0';
		  foreach ($ca_sitemap_arr as $v) {
				if (strpos($v[1], 'template_source') !== false) {
					 $dm = $v[24];
					 break;
				}
		  }

		  if (in_array($ca_action_id, $ca_user_actions) && Mobile::detect($dm))
				$f->template_source = str_replace('template_source', 'i_template_source', $f->template_source);
	 }

	 $contents = Formatter::fmtInTemplate($ca_template, $html_output, '', '', true, $include_counter_flag, false, (strpos($ca_template, '.php') !== false));
	 $contents = str_replace(Formatter::GFSAbi($contents, '<title>', '</title>'), '<title>' . (($title == '') ? $ca_lang_l['administration panel'] : $title) . '</title>', $contents);
	 if ($templ_root)
		  $contents = str_replace('</title>', '</title>' . F_LF
					 . '<base href="' . str_replace('documents/centraladmin.php', '', Linker::buildSelfURL('centraladmin.php')) . '"' . '>', $contents);
	 $contents = preg_replace("'<\?php.*?\?>'si", '', $contents);
	 return $contents;
}

function build_login_form($ms = '', $ref_url = '', $user_account = array()) {
	 global $thispage_id, $ca_lang_l, $f, $sr_enable, $ca_l_amp, $ca_pref, $ca_loginids;

	 $contents = '';
	 $lform_in_earea = false;
	 $pageid_info = get_page_info($thispage_id);

	 $curr_lang = $f->inter_languages_a[array_search($pageid_info[16], $f->site_languages_a)];
	 if ($curr_lang == '' && isset($_REQUEST['lang']))
		  $curr_lang = Formatter::stripTags($_REQUEST['lang']);
	 ca_update_language_set($curr_lang);
	 $ca_l_amp = ($curr_lang != '') ? '&amp;lang=' . $curr_lang : $ca_l_amp;
	 $r = isset($_REQUEST['r']) ? '&amp;r=1' : '';
	 $ca_l_amp.=$r;

	 $direct_flag = (isset($_POST['loginid']) && isset($_GET['pageid']) && !isset($_GET['indexflag']));
	 $prot_page_info = ($direct_flag) ? get_page_info(Formatter::stripTags(trim($_POST['loginid']))) : $pageid_info;
	 $prot_page_name = $prot_page_info[1];
	 $prot_page_inroot = (strpos($prot_page_name, '../') === false) ? true : false;
	 $login_page_inroot = false;
	 $doc_dir = ($prot_page_inroot ? 'documents/' : '../documents/');

	 foreach ($ca_loginids as $v) {
		  $login_page_info = get_page_info($v);
		  if ($login_page_info[22] == $pageid_info[22]) {
				$use_login_pageid = $v;
				break;
		  }
	 }

	 if ($direct_flag) {
		  $contents = File::read($prot_page_name);
	 } // login page (deprecated)
	 elseif (isset($use_login_pageid)) {	  // page with login form
		  $login_page_info = get_page_info($use_login_pageid);
		  if (in_array($login_page_info[4], array('136', '137', '138', '143', '144', '20'))) {
				$l_dir = (strpos($login_page_info[1], '../') === false) ? '' : '../' . Formatter::GFS($login_page_info[1], '../', '/') . '/';
				$login_page_name = $l_dir . $use_login_pageid . (Validator::checkProtection($login_page_info) > 1 ? '.php' : '.html');
		  } elseif ($login_page_info[4] == '18') {
				$l_dir = (strpos($login_page_info[1], '../') === false) ? '' : '../' . Formatter::GFS($login_page_info[1], '../', '/') . '/';
				$login_page_name = $l_dir . ($use_login_pageid + 1) . '.html';
		  } else
				$login_page_name = $login_page_info[1];

		  if (Mobile::detect($login_page_info[24])) {
				if (strpos($login_page_name, '/') === false)
					 $login_page_name = 'i_' . $login_page_name;
				else {
					 $t_name = substr($login_page_name, strrpos($login_page_name, '/') + 1);
					 $login_page_name = str_replace($t_name, 'i_' . $t_name, $login_page_name);
				}
		  }

		  $login_page_inroot = (strpos($login_page_name, '../') === false) ? true : false;
		  if ($login_page_inroot && (!$prot_page_inroot || $ca_pref == '../'))
				$login_page_name = '../' . $login_page_name;
		  $contents = File::read($login_page_name);
		  $contents = Formatter::clearMacros($contents, $login_page_info[4]);

		  if ($prot_page_inroot)
				$contents = str_replace('="../', '="', $contents);
		  if (!$prot_page_inroot && $login_page_inroot)
				$contents = str_replace(array('href="documents/centraladmin.php?', 'src="', 'url("images', 'url(images', 'url("extimages', 'url(extimages'), array('href="../documents/centraladmin.php?', 'src="../', 'url("../images', 'url(../images', 'url("../extimages', 'url(../extimages'), $contents);
	 }
	 else {  // default login
		  $contents = '<!--page--><div id="div_login_def"><form name="login_def" id="login_def" method="post" action="' . $doc_dir . 'centraladmin.php?pageid=' . $thispage_id . $ca_l_amp . ($ref_url != '' ? '&amp;ref_url=' . urlencode($ref_url) : '') . '">';	 //ref_url -event manager
		  $contents.=F_BR . '
		  <table align="center">
		  <tr>
				<td></td>
				<td>
					<span class="rvts8"><b>' . $ca_lang_l['protected area'] . '</b></span>' . F_BR . '
				</td>
		  </tr>
		  <tr>
				<td>
					 <span class="rvts8">' . $ca_lang_l['username'] . '</span>
					 <span class="rvts12 frmhint" id="login_def_pv_username"></span>
				</td>
				<td>
					 <input class="input1" type="text" name="pv_username" style="width:180px">
				</td>
		  </tr>
		  <tr>
				<td>
					<span class="rvts8">' . $ca_lang_l['password'] . '</span>
					<span class="rvts12 frmhint" id="login_def_pv_password"></span>
				</td>
				<td>
					<input class="input1" type="password" name="pv_password" style="width:180px">
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input class="input1" type="submit" value="' . $ca_lang_l['login'] . '">
					<span class="rvts12 frmhint" id="login_def_error"></span>
				</td>
			</tr>';

		  $contents.='<tr><td></td><td><p> ' . F_BR . '<a class="rvts12" href="' . $doc_dir . 'centraladmin.php?process=forgotpass' . $ca_l_amp . '">'
					 . $ca_lang_l['forgot password'] . '</a></p><p class="rvps1"><span class="rvts8">&nbsp;</span></p>';
		  if ($sr_enable) {
				$contents.='<p><a class="rvts12" href="' . $doc_dir . 'centraladmin.php?process=register' . $ca_l_amp . '">' . $ca_lang_l['not a member'] . ' ' . $ca_lang_l['register'] . '</a></p>';
		  }
		  $contents.="</td></tr></table></form></div><!--/page-->";
	 }

	 if ((!isset($_GET['pageid']) || isset($_GET['indexflag']) || $ref_url != '') && !$direct_flag) {
		  $rep_what = Formatter::GFSAbi($contents, 'method="post" action="', '">');	 // login form action fixation

		  $url_st = $doc_dir . "centraladmin.php?pageid=";
		  if (isset($_GET['indexflag']))
				$rep_with = $url_st . $thispage_id . "&amp;indexflag=index" . $ca_l_amp;
		  elseif (isset($_GET['pageid']) && $ref_url != '')
				$rep_with = $url_st . $thispage_id . $ca_l_amp . '&amp;ref_url=' . urlencode($ref_url);
		  else
				$rep_with = $prot_page_name;
		  $contents = str_replace($rep_what, 'method="post" action="' . $rep_with . '">', $contents);

		  if (in_array($prot_page_info[4], array('136', '137', '138', '143', '144', '20'))) {	 // Special PHP pages
				if (!$prot_page_inroot)
					 $f->dir = '../' . Formatter::GFS($prot_page_info[1], '../', '/') . '/';
				elseif (Validator::checkProtection($prot_page_info) == 1)
					 $f->dir = '../';
				else
					 $f->dir = '';
				$f->dir = str_replace('//', '/', $f->dir);
				$prot_page_name_fixed = $f->dir . $thispage_id . (Validator::checkProtection($prot_page_info) > 1 ? '.php' : '.html');
		  }
		  elseif ($prot_page_info[4] == '133') {
				if (!$prot_page_inroot)
					 $prot_page_name_fixed = $prot_page_name;
				elseif (Validator::checkProtection($prot_page_info) == 1)
					 $prot_page_name_fixed = '../' . $prot_page_name;
				else
					 $prot_page_name_fixed = $prot_page_name;
				$prot_page_name_fixed = str_replace('//', '/', $prot_page_name_fixed);
		  } else
				$prot_page_name_fixed = $prot_page_name;

		  if (Mobile::detect($prot_page_info[24])) {
				if (strpos($prot_page_name_fixed, '/') === false)
					 $prot_page_name_fixed = 'i_' . $prot_page_name_fixed;
				else {
					 $t_name = substr($prot_page_name_fixed, strrpos($prot_page_name_fixed, '/') + 1);
					 $prot_page_name_fixed = str_replace($t_name, 'i_' . $t_name, $prot_page_name_fixed);
				}
		  }

		  if (strpos($prot_page_name_fixed, '../') === false && isset($_GET['indexflag']))
				$prot_page_name_fixed = '../' . $prot_page_name_fixed;

		  if (file_exists($prot_page_name_fixed))
				$protpage_content = File::read($prot_page_name_fixed);
		  else
				$protpage_content = '<html><head><link type="text/css" href="../documents/textstyles_nf.css" rel="stylesheet"></head><body>missing</BODY></html>';
		  $contents = str_replace(array('<BODY', '</BODY>'), array('<body', '</body>'), $contents);

		  if (strpos($contents, '<!--page-->') !== false)
				$replace_with = Formatter::GFS($contents, '<!--page-->', '<!--/page-->');
		  else
				$replace_with = Formatter::GFS($contents, Formatter::GFSAbi($contents, '<body', '>'), '</body>');

		  $temp = Formatter::GFS($protpage_content, '<!--login-->', '<!--/login-->');
		  $float_login = strpos($temp, 'class="frm_login"') !== false;

		  $login_page_scripts = $float_login ? '' : Formatter::GFS($contents, '<!--scripts-->', '<!--endscripts-->');

		  if (strpos($protpage_content, '<!--page-->') !== false) {
				$for_replace = Formatter::GFS($protpage_content, '<!--page-->', '<!--/page-->');
		  } else
				$for_replace = Formatter::GFS($protpage_content, Formatter::GFSAbi($protpage_content, '<body', '>'), '</body>');
		  $contents = str_replace($for_replace, $replace_with, $protpage_content);

		  if (!isset($use_login_pageid) || $use_login_pageid != $thispage_id) {
				$temp_for_js = $login_page_scripts;
				$login_page_scripts_new = '';
				$temp_for_js = str_replace(Formatter::GFSAbi($temp_for_js, '<!--menu_java-->', '<!--/menu_java-->'), '', $temp_for_js);
				while (strpos($temp_for_js, '<script') !== false) {
					 $script_t = Formatter::GFSAbi($temp_for_js, '<script', '</script>');
					 if (strpos($contents, $script_t) === false)
						  $login_page_scripts_new.=$script_t;
					 $temp_for_js = str_replace($script_t, '', $temp_for_js);
				}
				while (strpos($temp_for_js, '<style') !== false) {
					 $style_t = Formatter::GFSAbi($temp_for_js, '<style', '</style>');
					 if (strpos($contents, $style_t) === false)
						  $login_page_scripts_new.=$style_t;
					 $temp_for_js = str_replace($style_t, '', $temp_for_js);
				}
				if (!empty($login_page_scripts_new))
					 $login_page_scripts = $login_page_scripts_new;
				$contents = str_replace('<!--endscripts-->', $login_page_scripts . '<!--endscripts-->', $contents);
		  }
		  $contents = str_replace(Formatter::GFS($contents, '<!--counter-->', '<!--/counter-->'), '', $contents);
		  $contents = preg_replace("'<\?php.*?\?>'si", '', $contents);
		  if (strpos($prot_page_info[1], '../') === false) {
				$dn = dirname($_SERVER['PHP_SELF']);
				$url = $f->http_prefix . Linker::getHost() . str_replace('//', '/', str_replace('documents', '', $dn == '\\' ? '' : $dn) . '/');
				$contents = str_replace('</title>', '</title>' . F_LF . '<base href="' . $url . '">', $contents);
		  }

		  $earea = Formatter::GFSAbi($contents, '<!--%areap(', '<!--areaend-->');
		  $cl = 'documents/centraladmin.php?pageid=';
		  if (((strpos($earea, 'action="../' . $cl) !== false) || (strpos($earea, 'action="' . $cl) !== false)) && !isset($use_login_pageid)) {
				$lform_in_earea = true;
				if (strpos($earea, 'action="../' . $cl) !== false) {
					 $act = Formatter::GFSAbi($earea, 'action="../' . $cl, '"');
					 $earea_new = str_replace($act, 'action="../' . $cl . $thispage_id . '"', $earea);
				} else {
					 $act = Formatter::GFSAbi($earea, 'action="' . $cl, '"');
					 $earea_new = str_replace($act, 'action="' . $cl . $thispage_id . '"', $earea);
				}
				$contents = str_replace($earea, $earea_new, $contents);
				$contents = str_replace(Formatter::GFS($contents, '<!--page-->', '<!--/page-->'), F_BR . '<div class="rvps1">' . $ca_lang_l['login form msg'] . '</div>', $contents);
		  }

		  if ($float_login) {
				$contents = str_replace(Formatter::GFS($contents, '<!--page-->', '<!--/page-->'), F_BR . '<div class="rvps1">' . $ca_lang_l['login form msg'] . '</div>', $contents);
				while (strpos($contents, '<!--%area') !== false)
					 $contents = str_replace(Formatter::GFSAbi($contents, '<!--%area', '<!--areaend-->'), '', $contents);
				if ($ref_url != '') {
					 $ext_form_area = Formatter::GFS($contents, '<!--login-->', '<!--/login-->');
					 $ext_form_area_new = str_replace('action="../' . $cl, 'action="../documents/centraladmin.php?ref_url=' . urlencode($ref_url) . '&amp;pageid=', $ext_form_area);
					 $ext_form_area_new = str_replace('action="' . $cl, 'action="documents/centraladmin.php?ref_url=' . urlencode($ref_url) . '&amp;pageid=', $ext_form_area_new);
					 $contents = str_replace($ext_form_area, $ext_form_area_new, $contents);
				}
				$contents = str_replace('float_login({});', 'float_login({op:true});', $contents);
		  }
	 }
	 $contents = str_replace(array('GMload();', 'GUnload();'), array('', ''), $contents);
	 if (!$direct_flag && !$lform_in_earea && !isset($float_login)) {
		  $cc_js_code = str_replace('%ID%', 'login_def', $f->loginvalidation);
		  $contents = str_replace('<!--scripts-->', '<!--scripts-->' . $cc_js_code, $contents);
	 }
	 return str_replace('</title>', '</title>' . F_LF . '<meta name="robots" content="noindex,nofollow">', $contents);
}

function build_menu($caption = '') {
	 global $ca_pref_dir, $ca_lang_l, $ca_l_amp, $f, $ca_action_id;

	 $url_base = $ca_pref_dir . 'centraladmin.php?process=';
	 $data = array();
	 $data[] = Navigation::addEntry($ca_lang_l['site map'], $url_base . "index" . $ca_l_amp, $ca_action_id == 'index', 'sitemap');
	 $data[] = Navigation::addEntry($ca_lang_l['manage users'], $url_base . "manageusers" . $ca_l_amp, in_array($ca_action_id, array('manageusers', 'pendingreg', 'processuser')), 'users');

	 if ($f->counter_on)
		  $data[] = Navigation::addEntry($ca_lang_l['counter settings'], $url_base . "confcounter" . $ca_l_amp, in_array($ca_action_id, array('confcounter', 'resetcounter')), 'counter');
	 $data[] = Navigation::addEntry($ca_lang_l['registration settings'], $url_base . "confreg" . $ca_l_amp, in_array($ca_action_id, array('confreg')), 'confreg');
	 $data[] = Navigation::addEntry($ca_lang_l['log'], $url_base . "log" . $ca_l_amp, in_array($ca_action_id, array('log', 'clearlog')), 'log');
	 $data[] = Navigation::addEntry($ca_lang_l['settings'], $url_base . "conflang" . $ca_l_amp, in_array($ca_action_id, array('conflang', 'confreglang', 'regfields')), 'settings');
	 $data[] = Navigation::addEntry($ca_lang_l['logout'], $url_base . "logoutadmin" . $ca_l_amp, $ca_action_id == 'logoutadmin', 'logout', ($f->admin_nickname != '' ? $f->admin_nickname : 'admin'), 'a_right last');

	 $output = Navigation::admin2($data, $caption);
	 return $output;
}

function build_myprofile_menu($caption = '') {
	 global $user, $ca_pref_dir, $ca_lang_l, $ca_l_amp, $ca_action_id, $thispage_id;

	 Session::intStart();
	 $logged_as_caadmin = Cookie::isAdmin();
	 $logged_user = $logged_as_caadmin ? Cookie::getAdmin() : $user->getUserCookie();

	 $url_base = $ca_pref_dir . 'centraladmin.php?pageid=' . $thispage_id . '&amp;username=' . $logged_user . '&amp;ref_url=' . (isset($_GET['ref_url']) ? urlencode($_GET['ref_url']) : '') . '&amp;process=';

	 $data = array();
	 $data[] = Navigation::addEntry($ca_lang_l['site map'], $url_base . "myprofile" . $ca_l_amp, $ca_action_id == 'myprofile', 'sitemap');

	 $data[] = Navigation::addEntry($ca_lang_l['profile'], $url_base . "editprofile" . $ca_l_amp, in_array($ca_action_id, array('editprofile', 'stat')), 'profile');

	 $data[] = Navigation::addEntry($ca_lang_l['change password'], $url_base . "changepass" . $ca_l_amp, $ca_action_id == 'changepass', 'changepass');

	 $data[] = Navigation::addEntry($ca_lang_l['logout'], $ca_pref_dir . 'centraladmin.php?process=logout&amp;pageid=' . $thispage_id . $ca_l_amp, $ca_action_id == 'changepass', 'logout', $logged_user, 'a_right last');

	 $output = Navigation::admin2($data);
	 return $output;
}

function build_login_form_ca($msg) {
	 global $ca_pref_dir, $ca_lang_l, $ca_l_amp, $f;
	 $vert = ($f->mobile_detected);
	 $output = '<div class="rvps1"><form method="post" action="' . $ca_pref_dir . 'centraladmin.php?process=index' . $ca_l_amp . '">';
	 $output.='<table align="center"><tr><td ' . ($vert ? '' : 'colspan="2"') . ' style="text-align:' . ($vert ? 'left' : 'center') . ';"><h1>' . $msg . F_BR . F_BR . '</h1></td></tr><tr><td><span class="rvts8">' . $ca_lang_l['username'] . '</span>' . ($vert ? F_BR : '</td><td>') . '<input class="input1" type="text" name="username" style="width:180px"></td></tr>'
				. '<tr><td><span class="rvts8">' . $ca_lang_l['password'] . '</span>' . ($vert ? F_BR : '</td><td>') . '<input class="input1" type="password" autocomplete="off" name="password" style="width:180px"></td></tr>';
	 $output.='<tr><td>' . ($vert ? '' : '</td><td>') . '<input class="input1" type="submit" name="login" value="' . $ca_lang_l['login'] . '">&nbsp;</td></tr></table></form></div>';
	 return $output;
}

function build_add_user_form($msg = '') {
	 global $ca_access_type_ex, $ca_pref_dir, $ca_lang_l, $ca_l_amp, $f, $ca_areaarray;

	 $username = (isset($_POST['save']) ? un_esc($_POST['username']) : '');
	 $input = '<input class="input1" type="text" name="%s" value="%s" style="width:280px" maxlength="255">' . F_BR;
	 $input_ps = '<input class="input1" type="password" autocomplete="off" name="%s" style="width:280px" maxlength="50">' . F_BR;
	 $table_data = array();

	 $output = '<form action="' . $ca_pref_dir . "centraladmin.php?process=processuser" . $ca_l_amp . '" method="post"><div style="text-align:left;">' . ($msg != '' ? $msg . F_BR : '');

	 $table_data[] = array($ca_lang_l['username'] . $f->fmt_star, sprintf($f->fmt_hidden, 'flag', 'add') . sprintf($f->fmt_hidden, 'old_username', $username) . sprintf($input, 'username', $username));

	 $table_data[] = array($ca_lang_l['name'], sprintf($input, 'name', (isset($_POST['save']) ? un_esc($_POST['name']) : '')));
	 $table_data[] = array($ca_lang_l['surname'], sprintf($input, 'sirname', (isset($_POST['save']) ? un_esc($_POST['sirname']) : '')));
	 $table_data[] = array($ca_lang_l['email'], sprintf($input, 'email', (isset($_POST['save']) ? $_POST['email'] : '')));
	 $table_data[] = array($ca_lang_l['password'] . $f->fmt_star, sprintf($input_ps, 'password'));
	 $table_data[] = array($ca_lang_l['repeat password'] . $f->fmt_star, sprintf($input_ps, 'repeatedpassword'));

	 // sections and access
	 $select_all_flag = (isset($_POST['select_all']) ? true : false);
	 $select_all_val = ($select_all_flag) ? $_POST["select_all"] : 'undefined';
	 $checked_all_read = (!$select_all_flag || $select_all_val == 'yes');
	 $checked_all_write = ($select_all_flag && $select_all_val == 'yesw');
	 $checked_selected = ($select_all_flag && $select_all_val == 'no');

	 $line_template = '<div style="width:260px;"><div style="position:relative;"><div style="padding-left:10px;min-height:20px;"><span class="rvts8">&nbsp;&nbsp;%s</span></div><div style="position:absolute;right:0px;width:120px;top:0px" align="right">%s</div></div></div>';

	 $access_line = '<input type="radio" name="select_all" value="yes" ' . ($checked_all_read ? 'checked="checked"' : '')
				. ' onclick="javascript:hide_div(\'selected_holder\');"> <span class="rvts8">' . $ca_lang_l['view all'] . "</span>" . F_BR;
	 $access_line.='<input type="radio" name="select_all" value="yesw" ' . ($checked_all_write ? 'checked="checked"' : '')
				. ' onclick="javascript:hide_div(\'selected_holder\');"> <span class="rvts8">' . $ca_lang_l['edit all'] . "</span>" . F_BR;
	 if (!empty($ca_areaarray) || !$f->use_prot_areas) {
		  $access_line.='<input type="radio" name="select_all" value="no" ' . ($checked_selected ? 'checked="checked"' : '') . ' onclick="javascript:show_div(\'selected_holder\');">'
					 . '<span class="rvts8"> ' . $ca_lang_l['page level'] . ' </span>' . F_BR;
	 } else {
		  $access_line.=F_BR . '<span class="rvts8">' . $ca_lang_l['adduser_msg1'] . '</span>';
	 }

	 $access_line.='<div id="selected_holder" style="display:' . ($checked_selected ? 'block' : 'none') . ';">';
	 if ($f->use_prot_areas) {
		  foreach ($ca_areaarray as $k => $v) {
				$cur_sec_id = $k;
				$cur_sec_name = $v;
				$secaccess_type = (isset($_POST['access_type' . $cur_sec_id]) ? $_POST['access_type' . $cur_sec_id] : (!$checked_selected ? '2' : '-1'));
				$access_line.=F_BR . sprintf($line_template, $cur_sec_name, Builder::buildSelect('access_type' . $cur_sec_id, $ca_access_type_ex, $secaccess_type, 'onchange="javascript:tS(\'' . $cur_sec_id . '\');"'));
				$access_line.='<div id="section' . $cur_sec_id . '" style="display:' . (($secaccess_type == '2') ? "block" : "none") . '">';
				$access_line.=check_section_range(0, $cur_sec_id) . '</div>';
		  }
	 } else
		  $access_line.='<div>' . check_section_range(0) . '</div>';

	 $access_line.='</div>' . F_BR . '<div style="width:300px;"><span class="rvts8"><b>' . $ca_lang_l['view'] . '</b></span><span class="rvts8"> - ' . $ca_lang_l['adduser_msg2'] . F_BR . '</span><span class="rvts8"><b>' . $ca_lang_l['edit'] . '</b></span><span class="rvts8"> - ' . $ca_lang_l['adduser_msg3'] . '</span></div>';
	 $table_data[] = array($ca_lang_l['access to'], $access_line);

	 $base = Linker::buildSelfURL('centraladmin.php');
	 $table_data[] = '<span class="rvts8">(' . $f->fmt_star . ') ' . $ca_lang_l['required fields'] . '</span>' . F_BR;
	 $end = ca_getformbuttons('save', true, 'onclick="document.location=\'' . $base . "?process=manageusers" . $ca_l_amp . '\'"') . F_BR;

	 $output.=Builder::addEntryTable($table_data, $end);
	 $output.="</div></form><script language=\"javascript\" type=\"text/javascript\">function tS(id){if(document.getElementById('access_type'+id).selectedIndex==2) document.getElementById('section'+id).style.display='block'; else document.getElementById('section'+id).style.display='none'; } function show_div(id){document.getElementById(id).style.display='block';} function hide_div(id){document.getElementById(id).style.display='none';}</script>";
	 return $output;
}

function build_edit_user_form($flag, $msg, $username, $usrid = 0, $data = '') {  //flags - add,editpass,editaccess,editdetails
	 global $ca_access_type_ex, $ca_pref_dir, $ca_lang_l, $ca_l_amp, $f, $ca_areaarray;

	 $input = '<input class="input1" type="text" name="%s" value="%s" style="width:280px">' . F_BR;
	 $input_ps = '<input class="input1" type="password" autocomplete="off" name="%s" style="width:280px">' . F_BR;
	 $span8_nobr = '<span class="rvts8 a_editcaption">%s</span>';
	 $span8 = $span8_nobr . F_BR;

	 $output = '<div style="text-align:left">';
	 $output.='<form action="' . $ca_pref_dir . "centraladmin.php?process=processuser" . $ca_l_amp . '" method="post">';
	 $output.=sprintf($f->fmt_hidden, 'flag', $flag) . ($msg != '' ? $msg . F_BR . F_BR : '');
	 $output.=($flag == 'editdetails' ? sprintf($span8, $ca_lang_l['username'] . $f->fmt_star) : '');
	 if ($usrid > 0)
		  $output.=sprintf($f->fmt_hidden, 'id', $usrid);

	 $is_d = is_array($data);

	 if ($flag == 'editdetails') {
		  $creation_date = $is_d ? $data['creation_date'] : $_POST['creation_date'];
		  $output.=sprintf($f->fmt_hidden, 'creation_date', $creation_date);
		  $output.=sprintf($f->fmt_hidden, 'sr', ($is_d ? $data['self_registered'] : $_POST['sr']));
		  $output.=sprintf($f->fmt_hidden, 'status', ($is_d ? $data['status'] : $_POST['status']));
	 }
	 if ($flag == 'editdetails')
		  $output.=sprintf($f->fmt_hidden, 'old_username', $username) . sprintf($input, 'username', $username);
	 elseif ($flag == 'editaccess')
		  $output.=sprintf($f->fmt_hidden, 'username', $username);
	 else
		  $output.=sprintf($f->fmt_hidden, 'username', $username);

	 if ($flag == 'editdetails') {
		  $output.=sprintf($span8, $ca_lang_l['name'])
					 . sprintf($input, 'name', ($is_d ? un_esc($data['first_name']) : (isset($_POST['save']) ? un_esc($_POST['name']) : '')));
		  $output.=sprintf($span8, $ca_lang_l['surname'])
					 . sprintf($input, 'sirname', ($is_d ? un_esc($data['surname']) : (isset($_POST['save']) ? un_esc($_POST['sirname']) : '')));
		  $output.=sprintf($span8, $ca_lang_l['email'])
					 . sprintf($input, 'email', ($is_d ? $data['email'] : (isset($_POST['save']) ? $_POST['email'] : '')));

		  if ($flag == 'editdetails') {
				$output.=CA::formatNotice($ca_lang_l['creation date'] . ': ' . ($creation_date != '' ? date('r', Date::tzone($creation_date)) : 'NA'), true);
		  }
	 }
	 if ($flag == 'editpass') {
		  $output.=sprintf($span8, $ca_lang_l['password'] . $f->fmt_star) . sprintf($input_ps, 'password');
		  $output.=sprintf($span8, $ca_lang_l['repeat password'] . $f->fmt_star) . sprintf($input_ps, 'repeatedpassword');
	 }
	 if ($flag == 'editaccess') {  // sections and access
		  $select_all_flag = (!$is_d && isset($_POST['select_all']));
		  $checked_all_read = ($flag == 'editaccess' && isset($data['access'][0]) && $data['access'][0]['section'] == 'ALL' && $data['access'][0]['type'] == '0');
		  $checked_all_write = ($flag == 'editaccess' && isset($data['access'][0]) && $data['access'][0]['section'] == 'ALL' && $data['access'][0]['type'] == '1');
		  $checked_selected = ($select_all_flag && $_POST["select_all"] == 'no' || isset($data['access'][0]) && $data['access'][0]['section'] != 'ALL' || !isset($data['access'][0]));

		  $line_template = '<div style="width:260px;"><div style="position:relative;"><div style="padding-left:10px;min-height:20px;"><span class="rvts8">&nbsp;&nbsp;%s</span></div><div style="position:absolute;right:0px;width:120px;top:0px" align="right">%s</div></div></div>';

		  $output.='<fieldset style="padding:3px;"><legend>' . sprintf($span8_nobr, $ca_lang_l['access to']) . $f->fmt_star . '</legend>';
		  $output.='<input type="radio" name="select_all" value="yes" ' . ($checked_all_read ? 'checked="checked"' : '')
					 . ' onclick="javascript:hide_div(\'selected_holder_' . $usrid . '\');"> <span class="rvts8">' . $ca_lang_l['view all'] . "</span>" . F_BR;
		  $output.='<input type="radio" name="select_all" value="yesw" ' . ($checked_all_write ? 'checked="checked"' : '')
					 . ' onclick="javascript:hide_div(\'selected_holder_' . $usrid . '\');"> <span class="rvts8">' . $ca_lang_l['edit all'] . "</span>" . F_BR;
		  if (!empty($ca_areaarray) || !$f->use_prot_areas) {
				$output.='<input type="radio" name="select_all" value="no" ' . ($checked_selected ? 'checked="checked"' : '') . ' onclick="javascript:show_div(\'selected_holder_' . $usrid . '\');">'
						  . '<span class="rvts8"> ' . $ca_lang_l['page level'] . ' </span>' . F_BR;
		  } else
				$output.=F_BR . '<span class="rvts8">' . $ca_lang_l['adduser_msg1'] . '</span>';

		  $selected_sec_ids = array();
		  $selected_sec_access = array();
		  if ($is_d) {
				foreach ($data['access'] as $k => $v) {
					 $selected_sec_ids[] = $v['section'];
					 $selected_sec_access[] = $v['type'];
				}
		  }

		  $output.='<div id="selected_holder_' . $usrid . '" style="display:' . ($checked_selected ? 'block' : 'none') . ';">';
		  if ($f->use_prot_areas) {
				foreach ($ca_areaarray as $k => $v) {
					 $cur_sec_id = $k;
					 $cur_sec_name = $v;
					 $secaccess_type = (!$checked_selected ? '2' : '-1');
					 if (isset($_POST['access_type' . $cur_sec_id]))
						  $secaccess_type = $_POST['access_type' . $cur_sec_id];
					 elseif (isset($data[0])) {
						  $index = array_search($cur_sec_id, $selected_sec_ids);
						  if ($index !== false)
								$secaccess_type = $selected_sec_access[$index];
					 }

					 $output.=F_BR . sprintf($line_template, $cur_sec_name, Builder::buildSelect('access_type' . $cur_sec_id . '_' . $usrid, $ca_access_type_ex, $secaccess_type, 'onchange="javascript:tS(\'' . $cur_sec_id . '_' . $usrid . '\');"'));
					 $output.='<div id="section' . $cur_sec_id . '_' . $usrid . '" style="display:' . (($secaccess_type == '2') ? "block" : "none") . '">';
					 $output.=check_section_range(0, $cur_sec_id, $usrid, $data) . "</div>";
				}
		  } else
				$output.='<div>' . check_section_range(0, '', $usrid, $data) . "</div>";

		  $output.='</div>' . F_BR . '<div style="width:300px;"><span class="rvts8"><b>' . $ca_lang_l['view'] . '</b></span><span class="rvts8"> - ' . $ca_lang_l['adduser_msg2'] . F_BR . '</span><span class="rvts8"><b>' . $ca_lang_l['edit'] . '</b></span><span class="rvts8"> - ' . $ca_lang_l['adduser_msg3'] . '</span></div>' . F_BR . F_BR . '</fieldset>';
	 }
	 if ($flag == 'editdetails') { // event manager
		  $calendar_categories = get_calendar_categories();
		  if (!empty($calendar_categories)) {
				$news_for = array();
				if (isset($data['news']) && !empty($data['news'])) {
					 foreach ($data['news'] as $val)
						  $news_for[] = $val['page'] . '%' . $val['cat'];
				}
				$output.=F_BR . '<fieldset style="padding:3px;width:270px;"><legend>' . sprintf($span8_nobr, $ca_lang_l['want to receive notification']) . '</legend>' . F_BR;
				foreach ($calendar_categories as $k => $v) {
					 $ckbox_value = $v['pageid'] . '%' . $v['catid'];
					 $output.='<input type="checkbox" name="news_for[]" value="' . $ckbox_value . '" ' .
								(in_array($ckbox_value, $news_for) ? 'checked="checked" ' : '') . '> <span class="rvts8">' . $v['pagename'] . ' - ' . $v['catname'] . '</span>' . F_BR;
				}
				$output.=F_BR . '</fieldset>';
		  }
	 }
	 $base = Linker::buildSelfURL('centraladmin.php');
	 $output.=F_BR . ca_getformbuttons('save', true, 'onclick="' . (($usrid > 0) ? 'sv(\'' . $flag . '_' . $usrid . '\');' : 'document.location=\'' . $base . "?process=manageusers" . $ca_l_amp . '\'') . '""');
	 $output.='</form></div>';
	 $output.="<script language=\"javascript\" type=\"text/javascript\">function tS(id){if(document.getElementById('access_type'+id).selectedIndex==2) document.getElementById('section'+id).style.display='block'; else document.getElementById('section'+id).style.display='none'; } function show_div(id){document.getElementById(id).style.display='block';} function hide_div(id){document.getElementById(id).style.display='none';}</script>";
	 return $output;
}

function build_register_form($float) {
	 global $ca_pref_dir, $f, $ca_l_amp, $trtdsp, $ca_ulang_id, $ca_action_id, $ca_lang;

	 $lang_r = $f->lang_reg[$ca_ulang_id];
	 $settings = Formatter::GFS($f->ca_settings, '<registration>', '</registration>');
	 $sr_termsofuse_urls = Formatter::GFS($settings, '<terms_url>', '</terms_url>');
	 $sr_notes = Formatter::GFS($settings, '<notes>', '</notes>');

	 $norm_reg = ($ca_action_id == 'register');
	 $vert = (isset($_REQUEST['vert']) || $f->mobile_detected);
	 $span8 = ($norm_reg ? 'class="rvts8"' : 'class="field_label"');
	 $input1 = ($norm_reg ? 'class="input1"' : 'class="field"');
	 $saving = isset($_POST['save']);
	 $trtdsp = '<tr><td class="col1"><span ' . $span8 . '>%s*</span>' . ($vert ? F_BR : '</td><td class="col2">');
	 $trtdsp.='<input ' . $input1 . ' type="%s" name="%s" value="%s" %s';
	 $isize = '';
	 if ($norm_reg)
		  $isize = ($vert) ? 'style="width:160px;" ' : 'style="width:240px;" ';

	 if ($sr_termsofuse_urls != '') {
		  if (strpos($sr_termsofuse_urls, '../') !== false && strpos($ca_pref_dir, '../') === false) {
				$sr_termsofuse_urls = str_replace('../', '', $sr_termsofuse_urls);
		  }
	 }
	 $output = F_BR . '<form id="selfreg" name="selfreg" action="' . ($norm_reg ? $ca_pref_dir : (isset($_GET['root']) && $_GET['root'] == '1' ? '' : '../') . 'documents/') . "centraladmin.php?process=" . $ca_action_id . $ca_l_amp . (isset($_GET['charset']) ? '&amp;charset=' . Formatter::sth(Formatter::stripTags($_GET['charset'])) : '') . '" method="post">';
	 $output.='<div' . ($norm_reg ? ' style="margin:0 auto;text-align:' . ($f->mobile_detected ? 'left' : 'center;width:50%') . '"' : '') . ' class="' . ($norm_reg ? "sr_register" : "sr_register2") . '"><table class="form_table">';
	 if ($norm_reg)
		  $output.='<tr><td colspan="2" style="text-align:' . ($f->mobile_detected ? 'left' : 'center') . ';"><h1>' . $lang_r['registration'] . "</h1>" . F_BR . '</td></tr>';

	 $val = $saving ? Formatter::sth(Formatter::stripTags($_POST['username'])) : '';
	 $output.=sprintf($trtdsp, $lang_r['username'], 'text', 'username', $val, $isize) . '><span class="rvts12 frmhint" id="selfreg_username"></span></td></tr>';

	 $val = $saving ? Formatter::sth(Formatter::stripTags($_POST['name'])) : '';
	 $output.=sprintf($trtdsp, $lang_r['name'], 'text', 'name', $val, $isize) . '><span class="rvts12 frmhint" id="selfreg_name"></span></td></tr>';

	 $val = $saving ? Formatter::sth(Formatter::stripTags($_POST['sirname'])) : '';
	 $output.=sprintf($trtdsp, $lang_r['surname'], 'text', 'sirname', $val, $isize) . '><span class="rvts12 frmhint" id="selfreg_sirname"></span></td></tr>';

	 $val = $saving ? Formatter::sth(Formatter::stripTags($_POST['email'])) : '';
	 $output.=sprintf($trtdsp, $lang_r['email'], 'text', 'email', $val, $isize) . '><span class="rvts12 frmhint" id="selfreg_email"></span></td></tr>';

	 $output.=sprintf($trtdsp, $lang_r['password'], 'password', 'password', '', $isize) . '><span class="rvts12 frmhint" id="selfreg_password"></span></td></tr>';
	 $output.=sprintf($trtdsp, $lang_r['repeat password'], 'password', 'repeatedpassword', '', $isize) . '><span class="rvts12 frmhint" id="selfreg_repeatedpassword"></span></td></tr>';
	 if ($norm_reg) {
		  $output.=sprintf($trtdsp, $lang_r['code'], 'text', 'captchacode', '', '80') . 'size="4" maxlength="4"> ';
		  $output.='<span class="captcha"></span><span class="rvts12 frmhint" id="selfreg_code"></span></td></tr>';
	 }

	 $trtd2 = ($vert) ? '<tr><td>' : '<tr><td></td><td>';
	 if (!empty($sr_termsofuse_urls)) {
		  $output.=$trtd2;
		  $sr_agree_msg_fixed = $lang_r['I agree with terms'];
		  if ($sr_termsofuse_urls != '') {
				$pattern = Formatter::GFS($sr_agree_msg_fixed, '%%', '%%');
				$sr_agree_msg_fixed = str_replace('%%' . $pattern . '%%', '<a class="rvts12" href="' . $sr_termsofuse_urls . '">' . $pattern . '</a>', $sr_agree_msg_fixed);
		  } else
				$sr_agree_msg_fixed = str_replace('%%', '', $sr_agree_msg_fixed);
		  $output.='<input type="checkbox" name="agree" value="agree"> <span ' . $span8 . '> *';
		  $output.=$sr_agree_msg_fixed . '</span></td></tr>';
	 }
	 $output.=$trtd2 . '<span ' . $span8 . '> </span><span class="rvts12 frmhint" id="selfreg_agree"></span></td></tr>';
	 if (isset($sr_notes) && !empty($sr_notes))
		  $output.='<tr><td></td><td align="left"><span ' . $span8 . '>' . $sr_notes . '</span></td></tr>';

	 $calendar_categories = get_calendar_categories($ca_lang);
	 if (!empty($calendar_categories)) { //event manager
		  $output.=$trtd2 . '<span ' . $span8 . '><b>' . $lang_r['want to receive notification'] . F_BR . ' </b></span></td></tr>';
		  foreach ($calendar_categories as $v) {
				$output.=$trtd2 . '<input type="checkbox" name="news_for[]" value="' . $v['pageid'] . '%' . $v['catid'] . '"> <span ' . $span8 . '>' . $v['pagename'] . ' - ' . $v['catname'] . '</span></td></tr>';
		  }
		  $output.=$trtd2 . '<span ' . $span8 . '> </span></td></tr>';
	 }
	 $output.=$trtd2 . '<span ' . $span8 . '>(*) ' . $lang_r['required fields'] . '</span></td></tr>';
	 $output.=$trtd2 . '<input ' . ($norm_reg ? $input1 : '') . ' type="submit" value="' . $lang_r['submit_btn'] . '"><span id="selfreg_error" class="rvts12 frmhint"></span><input type="hidden" name="save" value="save"></td></tr></table></div></form>';
	 return $output;
}

function build_forgotpass_form() {
	 global $ca_pref_dir, $ca_l_amp, $f, $ca_ulang_id, $ca_action_id;

	 $lang_r = $f->lang_reg[$ca_ulang_id];
	 if ($ca_action_id == 'forgotpass') {
		  $vert = ($f->mobile_detected);
		  $span8 = 'class="rvts8"';
		  $input1 = 'class="input1"';
		  $output = F_BR . '<form id="forgotpass" name="forgotpass" action="' . $ca_pref_dir . 'centraladmin.php?process=' . $ca_action_id . $ca_l_amp . '" method="post">'
					 . '<div style="margin: 0 auto;text-align:' . ($vert ? 'left;' : 'center;width:40%;') . '" class="sr_forgotpass">'
					 . '<table class="form_table"><tr><td ' . ($vert ? '' : 'colspan="2"') . ' style="text-align:' . ($vert ? 'left' : 'center') . ';">'
					 . '<h1>' . $lang_r['forgotten password'] . '</h1>' . F_BR
					 . '<span ' . $span8 . '>' . $lang_r['forgot password message'] . F_BR . F_BR . '</span></td></tr>'
					 . '<tr><td><span ' . $span8 . '>' . $lang_r['username'] . '</span>' . ($vert ? F_BR : '</td><td>')
					 . '<input ' . $input1 . ' type="text" name="username" value="' . (isset($_POST['submit']) ? Formatter::sth(Formatter::stripTags($_POST['username'])) : '')
					 . '" style="width:240px"><span id="forgotpass_username" class="rvts12 frmhint"></span></td></tr>'
					 . '<tr><td><span ' . $span8 . '>' . $lang_r['email'] . '</span>' . ($vert ? F_BR : '</td><td>')
					 . '<input ' . $input1 . ' type="text" name="email" value="' . (isset($_POST['submit']) ? Formatter::sth(Formatter::stripTags($_POST['email'])) : '') . '" style="width:240px">'
					 . '<span id="forgotpass_email" class="rvts12 frmhint"></span></td></tr>'
					 . '<tr><td>' . ($vert ? '' : '</td><td>') . '<input ' . $input1 . ' type="submit" value="' . $lang_r['submit_btn'] . '">'
					 . '<span id="forgotpass_error" class="rvts12 frmhint"></span><input type="hidden" name="save" value="save"></td></tr></table></div></form>';
		  return $output;
	 } else
		  return build_forgotpass_float();
}

function build_forgotpass_float() {
	 global $ca_l_amp, $f, $ca_ulang_id, $ca_action_id;

	 $lang_r = $f->lang_reg[$ca_ulang_id];
	 $span8 = 'class="field_label"';
	 $input1 = 'class="field"';
	 $pre = (isset($_GET['root']) && $_GET['root'] == '1') ? '' : '../';
	 $vert = isset($_REQUEST['vert']);
	 $glu = $vert ? F_BR : '</td><td>';
	 $r1 = '<tr><td class="row1"><span ' . $span8 . '>';

	 $output = F_BR . '<form id="forgotpass" name="forgotpass" action="' . $pre . 'documents/centraladmin.php?process=' . $ca_action_id . $ca_l_amp . '" method="post">'
				. '<div class="sr_forgotpass2">'
				. '<span ' . $span8 . '>' . $lang_r['forgot password message'] . F_BR . F_BR . '</span>'
				. '<table class="form_table" align="right">'
				. $r1 . $lang_r['username'] . '</span>'
				. $glu . '<input ' . $input1 . ' type="text" name="username" value="' . (isset($_POST['submit']) ? Formatter::sth(Formatter::stripTags($_POST['username'])) : '')
				. '" ><span id="forgotpass_username" class="rvts12 frmhint"></span></td></tr>'
				. $r1 . $lang_r['email'] . '</span>' . $glu
				. '<input ' . $input1 . ' type="text" name="email" value="' . (isset($_POST['submit']) ? Formatter::sth(Formatter::stripTags($_POST['email'])) : '') . '" >'
				. '<span id="forgotpass_email" class="rvts12 frmhint"></span></td></tr>'
				. '<tr><td>' . ($vert ? '' : '</td><td>') . '<input type="submit" value="' . $lang_r['submit_btn'] . '">'
				. '<span id="forgotpass_error" class="rvts12 frmhint"></span><input type="hidden" name="save" value="save">'
				. '</td></tr></table></div></form>';
	 return $output;
}

function build_editprofile_form($username, $data = '', $msg = '') {
	 global $ca_pref_dir, $ca_lang_l, $ca_l_amp, $f, $ca_lang;

	 $input = '<input class="input1" type="text" name="%s" value="%s" style="width:280px" maxlength="255">' . F_BR;
	 $creation_date = (!empty($data) ? $data['creation_date'] : $_POST['creation_date']);
	 $sr = (!empty($data) ? $data['self_registered'] : $_POST['sr']);
	 $status = (!empty($data) ? $data['status'] : $_POST['status']);

	 $table_data = array();
	 $table_data[] = array($ca_lang_l['name'] . $f->fmt_star, sprintf($f->fmt_hidden, 'username', $username)
		  . sprintf($f->fmt_hidden, 'creation_date', $creation_date) . sprintf($f->fmt_hidden, 'sr', $sr) . sprintf($f->fmt_hidden, 'status', $status)
		  . sprintf($input, 'name', ($data != '' ? un_esc($data['first_name']) : (isset($_POST['save']) ? un_esc($_POST['name']) : ''))));

	 $table_data[] = array($ca_lang_l['surname'] . $f->fmt_star, sprintf($input, 'sirname', ($data != '' ? un_esc($data['surname']) : (isset($_POST['save']) ? un_esc($_POST['sirname']) : ''))));
	 $table_data[] = array($ca_lang_l['email'] . $f->fmt_star, sprintf($input, 'email', ($data != '' ? $data['email'] : (isset($_POST['save']) ? $_POST['email'] : ''))));


	 $calendar_categories = get_calendar_categories($ca_lang);
	 if (!empty($calendar_categories)) {
		  $news_for = array();
		  if (isset($data['news']) && !empty($data['news'])) {
				foreach ($data['news'] as $val)
					 $news_for[] = $val['page'] . '%' . $val['cat'];
		  }

		  $news_line = '';
		  foreach ($calendar_categories as $v) {
				$ckbox_value = $v['pageid'] . '%' . $v['catid'];
				$news_line.="<input type='checkbox' name='news_for[]' value='" . $ckbox_value . "' " .
						  (in_array($ckbox_value, $news_for) ? "checked='checked' " : "") . "> <span class='rvts8'>" . $v['pagename'] . ' - ' . $v['catname'] . "</span>" . F_BR;
		  }
	 }

	 $table_data[] = '<span class="rvts8">(' . $f->fmt_star . ') ' . $ca_lang_l['required fields'] . '</span>';
	 $end = ca_getformbuttons('save', false) . F_BR;

	 $output = "<form action='" . $ca_pref_dir . "centraladmin.php?process=editprofile&amp;pageid=" . $_GET['pageid'] . "&amp;ref_url=" . $_GET['ref_url'] . $ca_l_amp . "' method='post'>" . ($msg != '' ? $msg . F_BR : '')
				. Builder::addEntryTable($table_data, $end);
	 $output.='</form>';
	 return $output;
}

# ------------ self-registration

function process_register($float) {
	 global $user, $sr_enable, $ca_db_file, $ca_l, $f, $ca_template_file, $sr_notif_enabled, $ca_site_url,
	 $template_in_root, $ca_ulang_id, $ca_lang_l, $ca_lang, $ca_action_id, $ca_allunamechars;

	 if (!$sr_enable) {
		  print GT(F_BR . '<span class="rvts8"><b>Self-registration is not enabled for this site.</b></span>');
		  exit;
	 }

	 $lang_f = $f->lang_f[$ca_ulang_id];
	 $lang_r = $f->lang_reg[$ca_ulang_id];
	 $terms_settings = Formatter::GFS($f->ca_settings, '<registration>', '</registration>');
	 $terms_settings = Formatter::GFS($terms_settings, '<terms_url>', '</terms_url>');
	 $errors = array();
	 $norm_reg = ($ca_action_id == 'register');
	 $output_is_from = false;

	 if (isset($_POST['save'])) { // send registration email
		  Session::intStart();
		  if ($norm_reg && !Session::isSessionSet($f->cap_id) && !Captcha::isRecaptchaPosted()) {
				echo "This is illegal operation. You are not allowed to register.";
				exit;
		  } else {
				foreach ($_POST as $k => $v) {
					 if (!is_array($v))
						  $_POST[$k] = trim($v);
				}
				$ccheck = isset($_POST['cc']) && $_POST['cc'] == '1';

				$useic = (!$f->uni && $f->charset_lang_map[$ca_lang] != 'iso-8859-1' && function_exists("iconv"));

				$post_user = Formatter::stripTags($_POST['username']);
				if (empty($_POST['username']))
					 $errors[] = ($ccheck ? 'username' . '|' : '') . $lang_f['Required Field'];
				elseif (!preg_match($ca_allunamechars, $post_user))
					 $errors[] = ($ccheck ? 'username' . '|' : '') . $lang_r['can contain only'];
				elseif (duplicated_user($post_user))
					 $errors[] = ($ccheck ? 'username' . '|' : '') . $lang_r['username exists'];

				if (empty($_POST['name']))
					 $errors[] = ($ccheck ? 'name' . '|' : '') . $lang_f['Required Field'];
				if (empty($_POST['sirname']))
					 $errors[] = ($ccheck ? 'sirname' . '|' : '') . $lang_f['Required Field'];
				if (empty($_POST['email']))
					 $errors[] = ($ccheck ? 'email' . '|' : '') . $lang_f['Required Field'];
				elseif (!empty($_POST['email']) && !Validator::validateEmail(Formatter::stripTags($_POST['email'])))
					 $errors[] = ($ccheck ? 'email' . '|' : '') . $lang_f['Email not valid'];

				if (empty($_POST['password']))
					 $errors[] = ($ccheck ? 'password' . '|' : '') . $lang_f['Required Field'];
				elseif (strlen(trim($_POST['password'])) < 5)
					 $errors[] = ($ccheck ? 'password' . '|' : '') . $lang_r['your password should be'];
				elseif (empty($_POST['repeatedpassword']))
					 $errors[] = ($ccheck ? 'repeatedpassword' . '|' : '') . $lang_r['repeat password'];
				elseif ($_POST['password'] != $_POST['repeatedpassword'])
					 $errors[] = ($ccheck ? 'repeatedpassword' . '|' : '') . $lang_r['password and repeated password'];
				elseif (strtolower($post_user) == strtolower($_POST['password']))
					 $errors[] = ($ccheck ? 'username' . '|' : '') . $lang_r['username equal password'];

				if ($norm_reg && !Captcha::isValid())
					 $errors[] = ($ccheck ? 'captchacode' . '|' : '') . $lang_f['Captcha Message'];
				if (!isset($_POST['agree']) && !empty($terms_settings))
					 $errors[] = ($ccheck ? 'agree' . '|' : '') . $lang_r['you must agree with terms'];

				if (!empty($errors))
					 $errors[] = ($ccheck ? 'error|' : '') . $lang_f['validation failed'];

				if ($ccheck) {
					 $errors_output = implode('|', $errors);
					 if ($useic)
						  $errors_output = iconv($f->charset_lang_map[$ca_lang], "utf-8", $errors_output);

					 if (count($errors) > 0) {
						  print '0' . $errors_output;
						  exit;
					 } else if ($norm_reg) {
						  print '1';
						  exit;
					 }
				}
				if (count($errors) > 0) {
					 $output = implode(F_BR, $errors) . build_register_form($float);
					 $output_is_from = true;
				} else {
					 $settings = Formatter::GFS($f->ca_settings, '<registration>', '</registration>');
					 $require_approval = Formatter::GFS($settings, '<require_approval>', '</require_approval>');
					 if ($require_approval == '')
						  $require_approval = '0';
					 $access = array();
					 $access_str = (strpos($settings, '<access>') !== false) ? Formatter::GFS($settings, '<access>', '</access>') : '';
					 if ($access_str != '')
						  $temp_access = explode('|', $access_str);
					 if (isset($temp_access)) {
						  foreach ($temp_access as $k => $v) {
								$t = explode('%%', $v);
								$page_level_str = Formatter::GFS($v, '(', ')');
								if (!empty($page_level_str))
									 $t[1] = str_replace('(' . $page_level_str . ')', '', $t[1]);
								if ($t[1] == '2') {
									 $page_level_arr = explode(';', $page_level_str);
									 foreach ($page_level_arr as $vv) {
										  $value = explode('%', $vv);
										  $page_access_arr [] = array('page' => $value[0], 'type' => $value[1]);
									 }
									 $access[] = array('section' => $t[0], 'type' => $t[1], 'page_access' => $page_access_arr);
								} else
									 $access[] = array('section' => $t[0], 'type' => $t[1]);
						  }
					 }

					 $uniqueid = md5(uniqid(mt_rand(), true));
					 $link = Linker::buildSelfURL('centraladmin.php') . '?id=' . $uniqueid . '&process=register' . $ca_l;
					 $message = str_replace(array("%CONFIRMLINK%", '%%USERNAME%%'), array('%confirmlink%', '%%username%%'), $ca_lang_l['sr_email_msg']);
					 $content = str_replace(array("##", '%confirmlink%', '%%site%%', '%%username%%'), array('<br>', '<a href="' . $link . '">' . $link . '</a>', $ca_site_url, $post_user), $message);
					 $subject = str_replace('%%site%%', $ca_site_url, $ca_lang_l['sr_email_subject']);

					 $send_to_email = Formatter::stripTags($_POST["email"]);
					 $sections = '';
					 $news = '';
					 if (empty($access)) {
						  $sections.='<access id="1" section="ALL" type="0"></access>';
					 } else {
						  foreach ($access as $k => $v) {
								$sections.='<access id="' . ($k + 1) . '" section="' . $v['section'] . '" type="' . $v['type'] . '">';
								if ($v['type'] == '2') {
									 foreach ($v['page_access'] as $key => $val)
										  $sections.='<p id="' . ($key + 1) . '" page="' . $val['page'] . '" type="' . $val['type'] . '">';
								}
								$sections.='</access>';
						  }
					 }

					 if (isset($_POST["news_for"])) { //event manager
						  foreach ($_POST["news_for"] as $k => $v) {
								if (strpos($v, '%') !== false) {
									 list($p, $c) = explode('%', $v);
								} else {
									 $p = $v;
									 $c = '';
								}
								$news.='<news id="' . ($k + 1) . '" page="' . $p . '" cat="' . $c . '"></news>';
						  }
					 }
					 $details = '<details email="' . Formatter::stripTags($_POST["email"]) . '" name="' . esc(Formatter::stripTags($_POST["name"])) . '" sirname="' . esc(Formatter::stripTags($_POST["sirname"])) . '" sr="1"' . ($require_approval == '1' ? ' status="0"' : ' status="1"') . '></details>';
					 $log_msg = 'success';

					 $result = MailHandler::sendMailCA($content, $subject, $send_to_email);
					 if ($result == "1") {
						  db_write_user('selfreg', $uniqueid, $post_user, crypt($_POST['password']), $sections, $details, $news); //event manager
						  $log_msg.=", email SENT";
						  $output = F_BR . '<div class="rvps1">' . ($norm_reg ? "<h5>" : '<span class="field_label">') . $lang_r['registration was successful'] . ($norm_reg ? "</h5>" : '</span>') . '</div>';
					 } else {
						  $log_msg = 'fail'; //user is not actually stored into db
						  $log_msg.=', email FAILED (' . Formatter::stripTags($result) . ')';
						  $output = F_BR . 'Email FAILED. Try again.';
					 }

					 write_log('reg', 'USER:' . $post_user, $log_msg);
				}
		  }
	 } elseif (isset($_GET['id'])) { // confirm registration
		  $file_contents = '<?php echo "hi"; exit; /*<users> </users>*/ ?>';
		  if (!$fp = fopen($ca_db_file, 'r+')) {
				print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD', $ca_db_file));
				exit;
		  }
		  flock($fp, LOCK_EX);
		  $fsize = filesize($ca_db_file);
		  if ($fsize > 0)
				$file_contents = fread($fp, $fsize);
		  $users = Formatter::GFS($file_contents, '<users>', '</users>');

		  $get_id = Formatter::stripTags($_GET['id']);
		  if (strpos($file_contents, '<user id="' . $get_id) !== false) {
				if ($users != '') {
					 $users_arr = $user->formatUsers($users);
					 $last = array_pop($users_arr);
					 $new_id = $last['id'] + 1;
				} else
					 $new_id = 1;
				$_user = Formatter::GFSAbi($file_contents, '<user id="' . $get_id . '"', '</user>');
				$username = Formatter::GFS($_user, 'username="', '"');
				$new_user = str_replace($get_id, $new_id, $_user);
				$new_user = str_replace('<details', '<details date="' . time() . '"', $new_user);  // creation date
				$file_contents = str_replace('</users>', $new_user . '</users>', $file_contents);
				$file_contents = str_replace($_user, '', $file_contents);

				ftruncate($fp, 0);
				fseek($fp, 0);
				if (fwrite($fp, $file_contents) === FALSE) {
					 print "Cannot write to file";
					 exit;
				}
				flock($fp, LOCK_UN);
				fclose($fp);
				$confirm_message = Formatter::GFS($f->ca_settings, '<confirm_message>', '</confirm_message>');
				$output = F_BR . "<span class='rvts8'>" . $lang_r['registration was completed'] . '</span>' . F_BR . $confirm_message;
				$log_msg = 'success';
				if ($sr_notif_enabled) {
					 $users = Formatter::GFS($file_contents, '<users>', '</users>');
					 $users_arr = $user->formatUsers($users);
					 if (!empty($users_arr)) {
						  foreach ($users_arr as $k => $v)
								if ($username == $v['username']) {
									 $user_data = $v;
									 break;
								}
					 }

					 $content = 'register_id= ' . Formatter::stripTags($_GET['id']) . '<br>' . 'username= ' . $user_data['username'] . '<br>';
					 $content.='name= ' . un_esc($user_data['first_name']) . '<br>' . 'surname= ' . un_esc($user_data['surname']) . '<br>';
					 $content.='email= ' . $user_data['email'] . '<br>' . 'date= ' . date('Y-m-d G:i', Date::tzone(time())) . '<br>';
					 $content.='IP= ' . Detector::getIP() . '<br>';
					 $content.='HOST= ' . Detector::getRemoteHost() . '<br>';
					 $content.='OS= ' . (isset($_SERVER['HTTP_USER_AGENT']) ? Detector::defineOS($_SERVER['HTTP_USER_AGENT']) : "") . '<br>';
					 $subject = str_replace('%%site%%', $ca_site_url, $ca_lang_l['sr_notif_subject']);

					 $result = MailHandler::sendMailCA($content, $subject);
					 if ($result == "1")
						  $log_msg.=', notification SENT';
					 else
						  $log_msg.=', notification FAILED (' . Formatter::stripTags($result) . ')';
				}
				if (!isset($_GET['flag']))
					 write_log('conf', 'USER:' . $username, $log_msg);
				else {
					 write_log('confadmin', 'USER:' . $username, $log_msg);
					 check_pending_users($output);
					 exit;
				}
		  } else
				$output = F_BR . "<h5>" . $lang_r['registration was completed'] . "</h5>";
	 }
	 else {
		  $output = build_register_form($float);
		  $output_is_from = true;
	 }

	 if ($norm_reg) {
		  $output = GT($output, false, $lang_r['registration'], true);
		  if ($output_is_from)
				$output = Builder::includeScript(str_replace('%ID%', 'selfreg', $f->frmvalidation), $output);
		  $rel_path = ($template_in_root ? '' : '../');

		  if (strpos($output, 'class="captcha') != false)
				$output = Builder::includeScript(str_replace('%PATH%', $rel_path, $f->captchajs), $output);
	 } else
		  $output = str_replace('%ID%', 'selfreg', $f->frmvalidation2) . F_LF . $output;
	 print $output;
}

function process_forgotpass() {
	 global $user, $f, $ca_lang_l, $ca_ulang_id, $ca_pref, $ca_db_file, $ca_template_file, $ca_lang,
	 $ca_db_settings_file, $ca_site_url, $ca_action_id;

	 $lang_f = $f->lang_f[$ca_ulang_id];
	 $lang_r = $f->lang_reg[$ca_ulang_id];
	 $norm_reg = ($ca_action_id == 'forgotpass');
	 $errors = array();
	 $ca_full_script_path = Linker::buildSelfURL('centraladmin.php');
	 if (isset($_POST['save'])) {
		  $ccheck = isset($_POST['cc']) && $_POST['cc'] == '1';
		  $useic = (!$f->uni && $f->charset_lang_map[$ca_lang] != 'iso-8859-1' && function_exists("iconv"));

		  if (!empty($_POST["username"])) {
				$usr = Formatter::stripTags(trim($_POST["username"]));
				$user_data = $user->getUser($usr, $ca_pref);
		  }
		  if (!empty($_POST["email"])) {
				$email = Formatter::stripTags(trim($_POST["email"]));
				$user_data = $user->getUser('', $ca_pref, $email);
		  }

		  if (!isset($usr) && !isset($email))
				$errors[] = ($ccheck ? 'username' . '|' : '') . $lang_r['you have to fill'];
		  elseif (isset($usr) && empty($user_data))
				$errors[] = ($ccheck ? 'username' . '|' : '') . $lang_r['unexisting'];
		  elseif (isset($email) && !Validator::validateEmail($email))
				$errors[] = ($ccheck ? 'username' . '|' : '') . $lang_f['Email not valid'];
		  elseif (isset($email) || isset($usr)) {
				if (!isset($user_data['email']) || $user_data['email'] == '')
					 $errors[] = ($ccheck ? 'username' . '|' : '') . $lang_r[isset($email) ? 'email not found' : 'no email for user'];
		  }

		  if ($ccheck) {
				$errors_output = implode('|', $errors);
				if ($useic)
					 $errors_output = iconv($f->charset_lang_map[$ca_lang], "utf-8", $errors_output);

				if (count($errors) > 0) {
					 print '0' . $errors_output;
					 exit;
				} else if ($norm_reg) {
					 print '1';
					 exit;
				}
		  }
		  if (count($errors) > 0)
				$output = implode(F_BR, $errors) . build_forgotpass_form();
		  else {
				$uniqueid = md5(uniqid(mt_rand(), true));
				$send_to_email = $user_data['email'];
				$confirm_url = $ca_full_script_path . '?process=forgotpass&confirm=' . $uniqueid;
				$confirm_link = '<a href="' . $confirm_url . '">' . $confirm_url . '</a>';
				File::writeTaggedData('fp_' . $uniqueid, $user_data['username'], $ca_db_settings_file, $ca_template_file);
				$content = str_replace(array('##', '%%confirmlink%%', '%%confirmurl%%', '%%site%%', '%%username%%', '%%USERNAME%%'), array('<br>', $confirm_link, $confirm_url, $ca_site_url, $user_data['username'], $user_data['username']), $ca_lang_l['sr_forgotpass_msg0']);
				$subject = str_replace('%%site%%', $ca_site_url, $ca_lang_l['sr_forgotpass_subject0']);
				$result = MailHandler::sendMailCA($content, $subject, $send_to_email);
				$output = F_BR . ($norm_reg ? "<h5>" : '<span class="field_label">') . $ca_lang_l['check email for instructions'] . ($norm_reg ? "</h5>" : '</span>');
		  }
	 } elseif (isset($_GET["confirm"])) {
		  $uniqueid = trim(Formatter::stripTags($_GET["confirm"]));
		  $new_pass = mt_rand();
		  $username = Formatter::GFS($f->ca_settings, '<fp_' . $uniqueid . '>', '</fp_' . $uniqueid . '>');
		  if (!empty($username)) {
				$user_data = $user->getUser($username, $ca_pref);
				$send_to_email = $user_data['email'];
				$content = str_replace(array("##", "%%newpassword%%", '%%site%%'), array('<br>', $new_pass, $ca_site_url), $ca_lang_l['sr_forgotpass_msg']);
				$content = str_replace(array('%%username%%', '%%USERNAME%%'), array($username, $username), $content);
				$subject = str_replace('%%site%%', $ca_site_url, $ca_lang_l['sr_forgotpass_subject']);
				$result = MailHandler::sendMailCA($content, $subject, $send_to_email);
				if ($result == "1") {
					 if (!$fp = fopen($ca_db_file, 'r+')) {
						  print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD', $ca_db_file));
						  exit;
					 }
					 flock($fp, LOCK_EX);
					 $file_contents = fread($fp, filesize($ca_db_file));

					 $users = Formatter::GFS($file_contents, '<users>', '</users>');
					 $old_data = Formatter::GFSAbi($users, '<user id="' . $user_data['id'] . '"', '</user>');
					 $new_data = str_replace(Formatter::GFSAbi($old_data, 'password="', '">'), 'password="' . crypt($new_pass) . '">', $old_data);
					 $file_contents = str_replace($old_data, $new_data, $file_contents);

					 ftruncate($fp, 0);
					 fseek($fp, 0);
					 if (fwrite($fp, $file_contents) === FALSE) {
						  print "Cannot write to file";
						  exit;
					 }
					 flock($fp, LOCK_UN);
					 fclose($fp);

					 $log_msg = "success, email SENT";
					 $output = F_BR . '<h5>' . $lang_r['check email for new password'] . '</h5>';
					 File::writeTaggedData('fp_' . $uniqueid, '', $ca_db_settings_file, $ca_template_file, true);
				} else {
					 $log_msg = 'success, email FAILED (' . Formatter::stripTags($result) . ')';
					 $output = 'Email FAILED. Try again.';
				}
				write_log('forgotpass', 'USER:' . $username, $log_msg);
		  } else
				$output = F_BR . '<h5>' . $lang_r['check email for new password'] . '</h5> <a class="rvts12" href="' . $ca_full_script_path . '?process=forgotpass' . '">' . $ca_lang_l['forgotten password'] . '</a>';
	 } else
		  $output = build_forgotpass_form();
	 if ($norm_reg) {
		  $output = GT($output, false, $lang_r['forgotten password'], true);
		  $output = Builder::includeScript(str_replace('%ID%', 'forgotpass', $f->frmvalidation), $output);
	 } else
		  $output = str_replace('%ID%', 'forgotpass', $f->frmvalidation2) . F_LF . $output;
	 print $output;
}

function process_changepass() {
	 global $user, $ca_pref, $ca_lang_l, $ca_db_file, $template_in_root, $ca_template_file;

	 $username = (Cookie::isAdmin()) ? Formatter::sth(Formatter::stripTags($_REQUEST['username'])) : $user->getUserCookie();
	 $user_data = $user->getUser($username, $ca_pref);
	 $msg = array();

	 if (isset($_POST['save'])) {
		  if (empty($_POST['oldpassword']))
				$msg['oldpassword'] = $ca_lang_l['fill in'] . ' ' . $ca_lang_l['old password'];
		  elseif ($user_data['password'] != crypt($_POST['oldpassword'], $user_data['password']))
				$msg['oldpassword'] = $ca_lang_l['wrong old'];

		  if (empty($_POST['newpassword']))
				$msg['newpassword'] = $ca_lang_l['fill in'] . ' ' . $ca_lang_l['new password'];
		  elseif (strlen(trim($_POST['newpassword'])) < 5)
				$msg['newpassword'] = $ca_lang_l['your password should be'];
		  elseif (empty($_POST['repeatedpassword']))
				$msg['repeatedpassword'] = $ca_lang_l['repeat password'];
		  elseif ($_POST['newpassword'] != $_POST['repeatedpassword'])
				$msg['repeatedpassword'] = $ca_lang_l['password and repeated password'];

		  if (!empty($msg))
				$output = build_changepass_form($username, $msg);
		  else {
				if (isset($user_data['username']) && $user_data['username'] == $username) {
					 if (!$fp = fopen($ca_db_file, 'r+')) {
						  print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD', $ca_db_file));
						  exit;
					 }
					 flock($fp, LOCK_EX);
					 $file_contents = fread($fp, filesize($ca_db_file));

					 $users = Formatter::GFS($file_contents, '<users>', '</users>');
					 $old_data = Formatter::GFSAbi($users, '<user id="' . $user_data['id'] . '"', '</user>');
					 $new_data = str_replace(Formatter::GFSAbi($old_data, 'password="', '">'), 'password="' . crypt($_POST['newpassword']) . '">', $old_data);
					 $file_contents = str_replace($old_data, $new_data, $file_contents);
					 ftruncate($fp, 0);
					 fseek($fp, 0);
					 if (fwrite($fp, $file_contents) == FALSE) {
						  print "Cannot write to file";
						  exit;
					 }
					 flock($fp, LOCK_UN);
					 fclose($fp);

					 $show_msg = '<span class="rvts8">' . $ca_lang_l['changes saved'] . '</span>';
					 if (isset($_GET['ref_url'])) {
						  $u = $_GET['ref_url'];
						  if (strpos($_GET['ref_url'], '/') === false && $template_in_root == false)
								$u = '../' . $u;
					 }
					 write_log('changepass', 'USER:' . $username, 'success');

					 $table_data = array();
					 $table_data[] = array('', $show_msg);
					 $output = Builder::addEntryTable($table_data);
				}
		  }
	 } else
		  $output = build_changepass_form($username, $msg);
	 $output = Formatter::fmtAdminScreen($output, build_myprofile_menu());
	 $output = GT($output, false, '', true);
	 print $output;
	 exit;
}

function build_changepass_form($username, $msg) {
	 global $ca_pref_dir, $ca_lang_l, $ca_l_amp, $f;

	 $hint = F_BR . '<span class="rvts12 frmhint">%s</span>';

	 $table_data = array();
	 $table_data[] = array($ca_lang_l['old password'] . $f->fmt_star, '<input class="input1" type="password" name="oldpassword" value="" style="width:220px">' . (isset($msg['oldpassword']) ? sprintf($hint, $msg['oldpassword']) : ''));
	 $table_data[] = array($ca_lang_l['new password'] . $f->fmt_star, '<input class="input1" type="password" name="newpassword" value="" style="width:220px">' . (isset($msg['newpassword']) ? sprintf($hint, $msg['newpassword']) : ''));
	 $table_data[] = array($ca_lang_l['repeat password'] . $f->fmt_star, '<input class="input1" type="password" name="repeatedpassword" value="" style="width:220px">' . (isset($msg['repeatedpassword']) ? sprintf($hint, $msg['repeatedpassword']) : ''));
	 $table_data[] = '<span class="rvts8">(' . $f->fmt_star . ') ' . $ca_lang_l['required fields'] . '</span>';
	 $end = ca_getformbuttons('save', false) . F_BR;

	 $output = '<form action="' . $ca_pref_dir . "centraladmin.php?process=changepass" . $ca_l_amp . "&amp;pageid=" . $_GET['pageid'] . "&amp;ref_url=" . $_GET['ref_url'] . '" method="post">' .
				Builder::addEntryTable($table_data, $end) . '</form>';
	 return $output;
}

function process_editprofile() {
	 global $user, $ca_pref, $ca_lang_l, $ca_db_file, $ca_template_file;

	 $msg = '';
	 if (Cookie::isAdmin())
		  $username = Formatter::sth(Formatter::stripTags($_REQUEST['username']));
	 else
		  $username = $user->getUserCookie();
	 $user_data = $user->getUser($username, $ca_pref);

	 if (isset($_POST['save'])) {
		  if (empty($_POST['name']))
				$msg.=F_BR . $ca_lang_l['fill in'] . ' ' . Formatter::strToUpper($ca_lang_l['name']);
		  if (empty($_POST['sirname']))
				$msg.=F_BR . $ca_lang_l['fill in'] . ' ' . Formatter::strToUpper($ca_lang_l['surname']);
		  if (empty($_POST['email']))
				$msg.=F_BR . $ca_lang_l['fill in'] . ' ' . Formatter::strToUpper($ca_lang_l['email']);

		  if ($msg != '')
				$output = build_editprofile_form($username, '', F_BR . sprintf('<span class="rvts8"><em style="color:red;">%s</em></span>', $msg));
		  else {
				if (isset($user_data['username']) && $user_data['username'] == $username) {
					 if (!$fp = fopen($ca_db_file, 'r+')) {
						  print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD', $ca_db_file));
						  exit;
					 }
					 flock($fp, LOCK_EX);
					 $file_contents = fread($fp, filesize($ca_db_file));

					 $users = Formatter::GFS($file_contents, '<users>', '</users>');
					 $old_data = Formatter::GFSAbi($users, '<user id="' . $user_data['id'] . '"', '</user>');
					 $new_details = '<details email="' . $_POST["email"] . '" name="' . $_POST["name"] . '" sirname="' . $_POST["sirname"]
								. '" date="' . $_POST["creation_date"] . '" sr="' . $_POST["sr"] . '" status="' . $_POST["status"] . '"></details>';
					 $new_data = str_replace(Formatter::GFSAbi($old_data, '<details', '</details>'), $new_details, $old_data);

					 $news = '';
					 if (isset($_POST["news_for"])) { //event manager
						  foreach ($_POST["news_for"] as $k => $v) {
								if (strpos($v, '%') !== false)
									 list($p, $c) = explode('%', $v);
								else {
									 $p = $v;
									 $c = '';
								}
								$news.='<news id="' . ($k + 1) . '" page="' . $p . '" cat="' . $c . '"></news>';
						  }
					 }
					 if (!empty($news)) {
						  if (strpos($new_data, '</news_data>') === false)  //event manager
								$new_data = str_replace('</details>', '</details><news_data>' . $news . '</news_data>', $new_data);
						  else
								$new_data = str_replace(Formatter::GFSAbi($old_data, '<news_data>', '</news_data>'), '<news_data>' . $news . '</news_data>', $new_data);
					 }
					 $file_contents = str_replace($old_data, $new_data, $file_contents);
					 ftruncate($fp, 0);
					 fseek($fp, 0);
					 if (fwrite($fp, $file_contents) == FALSE) {
						  print "Cannot write to file";
						  exit;
					 }
					 flock($fp, LOCK_UN);
					 fclose($fp);

					 $show_msg = '<span class="rvts8">' . $ca_lang_l['changes saved'] . '</span>';
					 if (isset($_GET['ref_url'])) {
						  $u = $_GET['ref_url'];
						  $u = str_replace('../', '', $u);  //m
					 }
					 write_log('editprofile', 'USER:' . $username, 'success');

					 if (isset($_POST['lang']))
						  setcookie($username . '_lang', strtoupper(Formatter::stripTags($_POST['lang'])), mktime(23, 59, 59, 1, 1, 2037), '/');

					 $table_data = array();
					 $table_data[] = array('', $show_msg);
					 $output = Builder::addEntryTable($table_data);
				}
		  }
	 } else
		  $output = build_editprofile_form($username, $user_data);
	 $output = Formatter::fmtAdminScreen($output, build_myprofile_menu());
	 $output = GT($output, false, '', true);
	 print $output;
	 exit;
}

function get_calendar_categories($lang = '') {
	 global $f;
	 $categories = array();
	 $calendar_pages = get_pages_list('136', $lang);
	 foreach ($calendar_pages as $v) {
		  $file_contents = '';
		  if (strpos($v['url'], '../') === false)
				$v['url'] = '../' . $v['url'];
		  $fp = @fopen($v['url'], 'r');
		  if ($fp) {
				$file_contents = fread($fp, 4096);
				fclose($fp);
		  }
		  if (!empty($file_contents)) {
				if (strpos($file_contents, '$em_enabled=TRUE;') !== false || strpos($file_contents, '$em_enabled=true;') !== false) {
					 $cat_ids_arr = $cat_names_arr = $cat_visib_arr = array();

					 $cal_settings = File::read('../' . $f->db_folder . $v['pageid'] . '_settings.ezg.php');
					 while (strpos($cal_settings, '<cat_') !== false) {
						  $cat_id = Formatter::GFS($cal_settings, '<cat_', '>');
						  settype($cat_id, 'integer');
						  $category_info = Formatter::GFS($cal_settings, '<cat_' . $cat_id . '>', '</cat_' . $cat_id . '>');
						  list($name, $color, $vis, $mark, $mark_color) = explode('%%', $category_info);
						  if ($cat_id > 0) {
								$cat_ids_arr[] = $cat_id;
								$cat_names_arr[] = $name;
								$cat_visib_arr[] = ($vis == '1' ? true : false);
						  }
						  $cal_settings = str_replace('<cat_' . $cat_id . '>' . $category_info . '</cat_' . $cat_id . '>', '', $cal_settings);
					 }
					 if (empty($cat_ids_arr)) {
						  $cat_ids_arr[] = 1;
						  $cat_names_arr[] = "General";
						  $cat_visib_arr[] = 'yes';
					 }

					 foreach ($cat_names_arr as $kk => $vv) {
						  if (isset($cat_visib_arr[$kk]) && $cat_visib_arr[$kk] == 'true' || $cat_visib_arr[$kk] == true)
								$categories[] = array('pageid' => $v['pageid'], 'pagename' => $v['name'], 'catid' => $cat_ids_arr[$kk], 'catname' => str_replace('"', '', $vv));
					 }
				}
		  }
	 }
	 return $categories;
}

# ---------- DB

function write_log($change, $data, $message = "") {
	 global $ca_db_activity_log;

	 $message = str_replace(F_LF, '', $message); //remove new lines if such
	 $time = time();
	 $ip = Detector::getIP();

	 $typechange = array("reg" => "Register", "conf" => "Confirmation", "confadmin" => "Confirmation (Admin)", "forgotpass" => "Forgotten pass", "changepass" => "Change pass", "editprofile" => "Edit profile", "resend" => "Confirmation email resend", "login" => "Login", "logout" => "Logout");
	 $currchange = $typechange[$change];
	 $record_line = "$time => $currchange -> $data => Result: $message => $ip";

	 clearstatcache();
	 if (!file_exists($ca_db_activity_log))
		  $handle = @fopen($ca_db_activity_log, 'w');
	 else
		  $handle = @fopen($ca_db_activity_log, 'a');

	 if (!$handle)
		  return;
	 else {
		  flock($handle, LOCK_EX);
		  if (filesize($ca_db_activity_log) == 0)
				$buf = "<?php echo 'hi'; exit; /*" . F_LF . $record_line . F_LF;
		  else {
				$buf = $record_line . F_LF;
		  }
		  fwrite($handle, $buf);
		  flock($handle, LOCK_UN);
		  fclose($handle);
	 }
}

function db_get_users($tag = 'users') {
	 global $ca_db_file;

	 $filename = $ca_db_file;
	 if (!file_exists($filename))
		  $filename = str_replace('../', '', $filename);
	 $src = File::read($filename);
	 $users = Formatter::GFS($src, '<' . $tag . '>', '</' . $tag . '>');
	 return $users;
}

function db_remove_user($usr, $flag = 'users') {
	 global $user, $ca_db_file, $ca_template_file;
	 $result = false;
	 $updated_users = '';
	 $users = db_get_users($flag);
	 if ($flag == 'users') {
		  if ($users != '')
				$users_arr = $user->formatUsers($users);
	 }
	 else {
		  if ($users != '')
				$users_arr = $users;
	 }

	 if (isset($users_arr) && !empty($users_arr)) {
		  if (!$fp = fopen($ca_db_file, 'r+')) {
				print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD', $ca_db_file));
				exit;
		  }
		  flock($fp, LOCK_EX);
		  $fsize = filesize($ca_db_file);
		  if ($fsize > 0)
				$file_contents = fread($fp, $fsize);

		  $updated_users = str_replace(Formatter::GFSAbi($users, '<user id="' . $usr . '"', '</user>'), '', $users);

		  $file_contents = str_replace($users, $updated_users, $file_contents);
		  ftruncate($fp, 0);
		  fseek($fp, 0);
		  if (fwrite($fp, $file_contents) === FALSE) {
				print "Cannot write to file";
				exit;
		  }
		  flock($fp, LOCK_UN);
		  fclose($fp);
		  $result = true;
	 }
	 return $result;
}

function db_write_user($flag, $uniqueid, $username = '', $pwd = '', $sections = '', $details = '', $news = '') {
	 global $user;
	 $users_arr = array();
	 if ($flag == 'selfreg') {
		  db_add_user($uniqueid, $username, $pwd, $sections, $details, $news, true);
	 } else {
		  $users = db_get_users();
		  if ($users != '')
				$users_arr = $user->formatUsers($users);
		  if (!empty($users_arr)) {
				foreach ($users_arr as $v) {
					 if ($uniqueid == $v['id']) {
						  $id = $v['id'];
						  break;
					 }
				}
		  }
		  if ($flag != 'add' && isset($id))
				db_edit_user($flag, $id, $username, $pwd, $sections, $details, $news);
		  else {
				$last = array_pop($users_arr);
				db_add_user($last['id'] + 1, $username, $pwd, $sections, $details, $news);
		  }
	 }
}

function db_add_user($id, $username, $pwd, $sections, $details, $news, $self_reg = false) {
	 global $ca_db_file, $ca_template_file;
	 $result = false;
	 $file_contents = '<?php echo "hi"; exit; /*<users> </users>*/ ?>';

	 $new_user = '<user id="' . $id . '" username="' . $username . '" password="' . $pwd . '"><access_data>' . $sections . '</access_data>' . ($news != '' ? '<news_data>' . $news . '</news_data>' : '') . $details . '</user>'; //event manager

	 if (!file_exists($ca_db_file)) {
		  print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('MISSING_DBFILE', $ca_db_file));
		  exit;
	 } else if (!$fp = fopen($ca_db_file, 'r+')) {
		  print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD', $ca_db_file));
		  exit;
	 }
	 flock($fp, LOCK_EX);
	 $fsize = filesize($ca_db_file);
	 if ($fsize > 0)
		  $file_contents = fread($fp, $fsize);

	 if ($self_reg == false) {
		  $file_contents = str_replace('</users>', $new_user . '</users>', $file_contents);
	 } else {
		  if (strpos($file_contents, '<selfreg_users>') === false) {
				$file_contents = str_replace('</users>', '</users><selfreg_users>' . $new_user . '</selfreg_users>', $file_contents);
		  } else {
				$file_contents = str_replace('</selfreg_users>', $new_user . '</selfreg_users>', $file_contents);
		  }
	 }
	 if (strpos($file_contents, '/*<users>') === FALSE) {
		  $file_contents = str_replace('<users>', '/*<users>', $file_contents);
		  $file_contents = str_replace('</users>', '</users>*/', $file_contents);
	 }

	 ftruncate($fp, 0);
	 fseek($fp, 0);
	 if (fwrite($fp, $file_contents) === FALSE) {
		  print "Cannot write to file";
		  exit;
	 }
	 flock($fp, LOCK_UN);
	 fclose($fp);
	 $result = true;
}

function db_edit_user($flag, $id, $username, $pwd = '', $sections = '', $details = '', $news = '') {  //edit user's password or access
	 global $ca_db_file, $ca_template_file;

	 $users = $file_contents = $fixed = '';

	 $users = db_get_users();
	 if (!$fp = fopen($ca_db_file, 'r+')) {
		  print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD', $ca_db_file));
		  exit;
	 }
	 flock($fp, LOCK_EX);
	 $fsize = filesize($ca_db_file);
	 if ($fsize > 0)
		  $file_contents = fread($fp, $fsize);

	 $user_to_update = '<user id="' . $id . '" ' . Formatter::GFS($users, '<user id="' . $id . '" ', '</user>') . '</user>';

	 if (strpos($user_to_update, '</access_data>') === false || strpos($user_to_update, '<user id="' . ($id + 1) . '"') !== false) {
		  $fixed = $user_to_update;
		  if (strpos($user_to_update, '</access><access_data>') !== false) {
				$fixed = str_replace('</access><access_data>', '</access></access_data>', $user_to_update);
		  } else {
				if (strpos($user_to_update, '<user id="' . ($id + 1) . '"') !== false) {
					 $fixed = str_replace('<user id="' . ($id + 1) . '"', '</access_data> <details email="" name="" sirname="" date=""></details> </user> <user id="' . ($id + 1) . '"', $user_to_update);
				}
		  }
		  $file_contents = str_replace($user_to_update, $fixed, $file_contents);
		  ftruncate($fp, 0);
		  fseek($fp, 0);
		  if (fwrite($fp, $file_contents) === FALSE) {
				print "Cannot write to file";
				exit;
		  }
		  flock($fp, LOCK_UN);
		  fclose($fp);

		  $users = db_get_users();

		  if (!$fp = fopen($ca_db_file, 'r+')) {
				print "Cannot open file";
				exit;
		  }
		  flock($fp, LOCK_EX);
		  $fsize = filesize($ca_db_file);
		  if ($fsize > 0)
				$file_contents = fread($fp, $fsize);
	 }

	 if ($flag == 'editpass')
		  $updated_user = str_replace(Formatter::GFS($user_to_update, 'password="', '"'), $pwd, $user_to_update);
	 elseif ($flag == 'editaccess')
		  $updated_user = str_replace(Formatter::GFS($user_to_update, '<access_data>', '</access_data>'), $sections, $user_to_update);
	 elseif ($flag == 'editdetails') {
		  $updated_user = str_replace(Formatter::GFSAbi($user_to_update, '<details ', '></details>'), $details, $user_to_update);

		  if (strpos($user_to_update, '</news_data>') === false)  //event manager
				$updated_user = str_replace('</details>', '</details><news_data>' . $news . '</news_data>', $updated_user);
		  else
				$updated_user = str_replace(Formatter::GFSAbi($user_to_update, '<news_data>', '</news_data>'), '<news_data>' . $news . '</news_data>', $updated_user);
		  if (isset($_POST['old_username'])) {
				$old_user_name = Formatter::GFSAbi($updated_user, 'username="', '"');
				$updated_user = str_replace($old_user_name, 'username="' . $_POST['username'] . '"', $updated_user);
		  }
	 } elseif ($flag == 'activate' || $flag == 'block') {
		  $details_orig = Formatter::GFSAbi($user_to_update, '<details ', '>');
		  if (strpos($details_orig, 'status="') !== false) {
				$details_new = str_replace(Formatter::GFSAbi($details_orig, 'status="', '"'), 'status="' . ($flag == 'activate' ? '1' : '0') . '"', $details_orig);
		  } else {
				$details_new = str_replace('>', ' status="' . ($flag == 'activate' ? '1' : '0') . '">', $details_orig);
		  }

		  $updated_user = str_replace($details_orig, $details_new, $user_to_update);
	 } else
		  $updated_user = $user_to_update;

	 $file_contents = str_replace($user_to_update, $updated_user, $file_contents);
	 ftruncate($fp, 0);
	 fseek($fp, 0);
	 if (fwrite($fp, $file_contents) === FALSE) {
		  print "Cannot write to file";
		  exit;
	 }
	 flock($fp, LOCK_UN);
	 fclose($fp);

	 return true;
}

# ----------- login/logout

function login_admin() {  // process login  admin
	 global $ca_admin_username, $ca_admin_pwd, $ca_lang_l, $ca_account_msg;

	 $output = '';
	 if (isset($_POST['login'])) {
		  if (isset($_POST['password']))
				$pass_filled = md5($_POST['password']);
		  if (empty($_POST['username']) || empty($_POST['password'])) {
				$output.=build_login_form_ca("<em style='color:red;'>" . $ca_lang_l['fill in'] . ' ' . $ca_lang_l['username'] . ' & ' . $ca_lang_l['password'] . "</em>");
		  } elseif (Formatter::stripTags($_POST['username']) != $ca_admin_username || $pass_filled != $ca_admin_pwd) {
				set_delay();
				$output.=build_login_form_ca("<em style='color:red;'>" . $ca_lang_l['incorrect username/password'] . "</em>");
		  } else {
				Session::regenerateID();
				Cookie::setAdmin($ca_admin_username); //ADMIN
				if (isset($_SERVER['HTTP_USER_AGENT']))
					 Session::setVar('HTTP_USER_AGENT', md5($_SERVER['HTTP_USER_AGENT']));
				set_admin_cookie(); // for counter - to ignore hits from site admin
				index();
				exit;
		  }
	 }
	 else {
		  if (strtolower($ca_admin_username) == 'admin' && ($ca_admin_pwd == md5('admin') || $ca_admin_pwd == md5('Admin') || $ca_admin_pwd == md5('ADMIN'))) {
				print GT($ca_account_msg);
				exit;
		  }
		  $output.=build_login_form_ca($ca_lang_l['administration panel']);
	 }
	 $output = GT($output, false, '', true);
	 print $output;
}

function set_admin_cookie() {
	 if (!isset($_COOKIE['visit_from_admin'])) {  // counter needed to ignore hits from site admin
		  $ts = time();
		  $expire_ts = mktime(23, 59, 59, date('n', $ts), date('j', $ts), 2037);
		  setcookie('visit_from_admin', md5(uniqid(mt_rand(), true)), $expire_ts, '/');
	 }
}

function set_delay() {
	 global $ca_db_delay_file;

	 $max_exec = intval(ini_get('max_execution_time'));
	 $delay = ($max_exec >= 25) ? 20 : $max_exec - 2;
	 $ts = time();
	 $last_wrong_ts = $ts;

	 if (file_exists($ca_db_delay_file) && is_writable($ca_db_delay_file)) {
		  $fsize = filesize($ca_db_delay_file);
		  if ($fsize > 0) {
				$fp = fopen($ca_db_delay_file, 'r');
				$last_wrong_ts = intval(fread($fp, $fsize));
				fclose($fp);
		  }
		  if ($ts - $last_wrong_ts <= 30)
				sleep($delay);
		  else
				sleep(1);
		  $fp = fopen($ca_db_delay_file, 'w');
		  flock($fp, LOCK_EX);
		  fwrite($fp, $ts);
		  flock($fp, LOCK_UN);
		  fclose($fp);
	 }
	 elseif ($ts - $last_wrong_ts <= 30)
		  sleep($delay);
}

function logout_user() {
	 global $user, $f, $ca_action_id;

	 if ($ca_action_id == 'logoutadmin')
		  write_log('logout', 'USER:Administrator', 'success');
	 if ($ca_action_id == 'logout' && Cookie::isAdmin())
		  write_log('logout', 'USER:Administrator', 'success');
	 elseif ($user->userCookie()) {
		  $user = $user->getUserCookie();
		  write_log('logout', 'USER:' . $user, 'success');
	 }

	 Session::unsetSession();
	 $logout_redirect_url = Formatter::GFS($f->ca_settings, '<logout_redirect_url>', '</logout_redirect_url>');

	 if (!empty($logout_redirect_url)) {
		  $redirect_page_name = (strpos($logout_redirect_url, 'http') === false ? 'http://' : '') . $logout_redirect_url;
	 } elseif (isset($_GET['ref_url'])) {
		  $redirect_page_name = Formatter::stripTags($_GET['ref_url']);
	 } elseif (isset($_GET['pageid']) && intval($_GET['pageid']) > 0) {
		  $prot_page_info = get_page_info($_GET['pageid']);
		  $prot_page_name = $prot_page_info[1];
		  $redirect_page_name = (strpos($prot_page_name, '../') === false ? '../' : '') . $prot_page_name;
	 } else {
		  $pos = strpos($f->home_page, 'http://');
		  $redirect_page_name = ($pos !== false) ? substr($f->home_page, $pos) : '../' . $f->home_page;
	 }
	 Linker::redirect($redirect_page_name, false);
}

function user_navigation($only_username = false, $return_flag = false) {
	 global $user, $thispage_id, $ca_l_amp, $ca_pref, $f;

	 $labels = CA::getMyprofileLabels($thispage_id, $ca_pref);
	 $logged_as_label = (isset($_GET['logged_l']) ? Formatter::sth(Formatter::stripTags($_GET['logged_l'])) : 'logged as');
	 $pageid_info = CA::getPageParams($thispage_id, $ca_pref);
	 $root = isset($pageid_info[1]) && strpos($pageid_info[1], '../') === false;
	 $thispage_dir = $root ? 'documents/' : '../documents/';

	 $is_admin = Cookie::isAdmin();
	 $is_user = $user->userCookie();
	 if ($is_admin)
		  $username = Cookie::getAdmin();
	 elseif ($is_user)
		  $username = $user->getUserCookie();

	 $heading = '';
	 if (strtolower($logged_as_label) == 'username' || $only_username)
		  $heading = $username;
	 elseif ($is_admin || $is_user) {
		  $ca_url = $thispage_dir . 'centraladmin.php?process=';
		  $ref_url = (isset($pageid_info[1]) ? $pageid_info[1] : '');

		  if (strpos($labels['welcome'], '%%username%%') !== false)
				$heading.='<span class="rvts8">' . str_replace('%%username%%', $username, $labels['welcome']) . ' </span> ';
		  else
				$heading.='<span class="rvts8">' . $labels['welcome'] . ' [' . $username . '] </span> ';
		  $sp_page = isset($pageid_info[4]) && in_array($pageid_info[4], $f->sp_pages_ids);
		  if ($is_admin) {
				if ($sp_page)
					 $heading.='| <a class="rvts12" href="' . CA::defineAdminLink($pageid_info) . $ca_l_amp . '">' . $labels['edit'] . '</a>';
				$heading.='| <a class="rvts12" href="' . $ca_url . 'index' . $ca_l_amp . '">' . $labels['administration panel'] . '</a> '
						  . '| <a class="rvts12" href="' . $ca_url . 'logoutadmin&amp;pageid=' . $thispage_id . $ca_l_amp . '">' . $labels['logout'] . '</a>';
		  }
		  else {
				if ($sp_page && $user->hasWriteAccess($username, $pageid_info, $ca_pref))
					 $heading.='| <a class="rvts12" href="' . CA::defineAdminLink($pageid_info) . $ca_l_amp . '">' . $labels['edit'] . '</a>';
				$ca_detailed_url = $thispage_dir . 'centraladmin.php?pageid=' . $thispage_id . '&amp;ref_url=' . urlencode($ref_url) . '&amp;username=' . $username . $ca_l_amp . '&amp;process=';
				$heading.='| <a class="rvts12" href="' . $ca_detailed_url . 'myprofile">' . $labels['profile'] . '</a>'
						  . '| <a class="rvts12" href="' . $ca_url . 'logout&amp;pageid=' . $thispage_id . $ca_l_amp . '">' . $labels['logout'] . '</a>';
		  }
	 }

	 if ($return_flag)
		  return $heading;
	 else
		  print $heading;
}

function user_navigation_float($return_flag = false) {
	 global $user, $thispage_id, $ca_l_amp, $ca_pref, $f, $sr_enable;

	 $vert = isset($_REQUEST['vert']);
	 $glu = $vert ? '' : ' | ';
	 $labels = CA::getMyprofileLabels($thispage_id, $ca_pref);
	 $logged_as_label = (isset($_GET['logged_l']) ? Formatter::sth(Formatter::stripTags($_GET['logged_l'])) : 'logged as');
	 $pageid_info = CA::getPageParams($thispage_id, $ca_pref);
	 $root = isset($pageid_info[1]) && strpos($pageid_info[1], '../') === false;
	 $thispage_dir = $root ? 'documents/' : '../documents/';

	 $is_admin = Cookie::isAdmin();
	 $is_user = $user->userCookie();
	 if ($is_admin)
		  $username = Cookie::getAdmin();
	 elseif ($is_user)
		  $username = $user->getUserCookie();

	 if (strtolower($logged_as_label) == 'username')
		  $heading = $username;
	 elseif ($is_admin || $is_user) {
		  $ca_url = $thispage_dir . 'centraladmin.php?process=';
		  $ref_url = (isset($pageid_info[1]) ? $pageid_info[1] : '');

		  $heading = '<li>' . $labels['welcome'] . ' [' . $username . '] </li>';
		  if ($is_admin) {
				if (isset($pageid_info[4]) && in_array($pageid_info[4], $f->sp_pages_ids))
					 $heading.='<li>' . $glu . '<a href="' . CA::defineAdminLink($pageid_info) . $ca_l_amp . '">' . $labels['edit'] . '</a></li>';
				$heading.='<li>' . $glu . '<a href="' . $ca_url . 'index' . $ca_l_amp . '">' . $labels['administration panel'] . '</a></li>';
				$heading.='<li class="logout_float">' . $glu . '<a href="' . $ca_url . 'logoutadmin&amp;pageid=' . $thispage_id . $ca_l_amp . '">' . $labels['logout'] . '</a></li>';
		  }
		  else {
				$heading = '';
				if (isset($pageid_info[4]) && in_array($pageid_info[4], $f->sp_pages_ids) && $user->hasWriteAccess($username, $pageid_info, $ca_pref))
					 $heading.='<li>' . $glu . '<a href="' . CA::defineAdminLink($pageid_info) . $ca_l_amp . '">' . $labels['edit'] . '</a></li>';
				$ca_detailed_url = $thispage_dir . 'centraladmin.php?pageid=' . $thispage_id . '&amp;ref_url=' . urlencode($ref_url) . '&amp;username=' . $username . $ca_l_amp . '&amp;process=';
				$heading.='<li>' . $glu . '<a href="' . $ca_detailed_url . 'myprofile">' . $labels['profile'] . '</a></li>';
				$heading.='<li class="logout_float">' . $glu . '<a href="' . $ca_url . 'logout&amp;pageid=' . $thispage_id . $ca_l_amp . '">' . $labels['logout'] . '</a></li>';
		  }
	 } else
		  $heading = '<li>' . $labels['welcome guest'] . '</li>';

	 if (isset($_REQUEST['lang']) && in_array($_REQUEST['lang'], $f->inter_languages_a))
		  $lang = $_REQUEST['lang'];
	 else
		  $lang = $f->inter_languages_a[0];
	 $root = intval($_REQUEST['root']);

	 $reg_links = '';
	 if ($sr_enable) {
		  $ca_detailed_url = $thispage_dir . 'centraladmin.php?process=';
		  $reg_links = $labels['not a member'] .
					 ' <a id="login_register" href="' . $ca_detailed_url . 'register2&amp;lang' . $lang . '&amp;root=' . ($root ? '1' : '0') . '">' . $labels['register'] . '</a> ';
		  $reg_links.='| <a id="forgot_pwd" href="' . $ca_detailed_url . 'forgotpass2&amp;lang' . $lang . '">' . $labels['forgot password'] . '</a>';
	 }

	 if ($return_flag)
		  return array($heading, $reg_links);
	 else
		  print $heading;
}

function get_userpages() {
	 global $user, $f, $ca_pref;

	 $result = '';
	 Session::intStart("private");
	 if (Cookie::isAdmin())
		  $result = 'all';
	 elseif ($user->userCookie()) {
		  $user_account = $user->getUser($user->getUserCookie(), $ca_pref);

		  if ($user_account['access'][0]['section'] == 'ALL')
				$result = 'all';
		  else {
				$controlled_pages = get_prot_pages_list('');
				$protected_pages = array();
				$protected_pages_per_section = array();
				$special_ids = array();

				foreach ($controlled_pages as $k => $v) {
					 if ($v['protected'] == 'TRUE')
						  $protected_pages[] = $v['id'];
					 if (in_array($v['typeid'], $f->sp_pages_ids))
						  $special_ids[] = $v['id'];
				}

				foreach ($user_account['access'][0]['page_access'] as $k => $v) {

					 $pid = intval($v['page']);
					 if (in_array($pid, $protected_pages)) {
						  $at = intval($v['type']);
						  if (in_array($pid, $special_ids))
								$access = ($at == 1) || ($at == 3) || ($at == 0);
						  else
								$access = $at == 0;
						  if ($access)
								$result.=$pid . '|';
					 }
					 elseif ($v['section'] != 'ALL' && $pid == 0) { //protection sections
						  $vs = $v['section'];
						  if (!isset($protected_pages_per_section[$vs]))
								$protected_pages_per_section[$vs] = get_prot_pages_list($vs);
						  $protected_insection = $protected_pages_per_section[$vs];
						  $pp_section = $sp_section = array();
						  foreach ($protected_insection as $k2 => $v2) {
								if ($v2['protected'] == 'TRUE')
									 $pp_section[] = $v2['id'];
								if (in_array($v2['typeid'], $f->sp_pages_ids))
									 $sp_section[] = $v2['id'];
						  }
						  $at = intval($v['access_type']);
						  if ($at == 0)
								foreach ($pp_section as $k2 => $v2)
									 $result.=$v2 . '|';
						  foreach ($special_ids as $k2 => $v2)
								if ($at == 1 || $at == 3 || $at == 0)
									 $result.=$v2 . '|';
					 }
				}
		  }
	 }
	 if ($result == '')
		  $result = 'none';
	 return $result;
}

function process_admin() {
	 global $user, $ca_admin_username, $ca_pref, $ca_admin_pwd, $thispage_id, $version, $f, $ca_account_msg, $ca_db_settings_file,
	 $counter_ds_db_fname, $ca_db_activity_log, $ca_template_file, $ca_pref_dir, $ca_lang_l, $rss_call_in_prot_page,
	 $counter_ts_db_fname, $ca_l_amp, $ca_span8, $ca_lang_set_fname, $template_in_root, $ca_action_id,
	 $ca_lang, $ca_reg_lang_settings_keys, $ca_reg_lang_settings_labels, $ca_loggedcheck, $ca_areaarray, $ca_logged_access, $ca_l, $ca_myprofile_actions,
	 $ca_site_url, $ca_other_actions, $ca_admin_actions, $ca_lang_template, $ca_user_actions;

	 $access_flag = false;
	 $forms = array_merge($f->subminiforms, $f->subminiforms_news);
	 $ca_action_id = (empty($_GET) && empty($thispage_id)) ? 'index' : '';
	 $ca_action_id = (isset($_REQUEST['process']) ? Formatter::stripTags($_REQUEST['process']) : $ca_action_id);

	 if ($ca_action_id == 'up') {
		  echo get_userpages();
		  exit;
	 }
	 if (($ca_action_id != '') && !in_array($ca_action_id, $ca_other_actions) && !in_array($ca_action_id, $ca_admin_actions))
		  $ca_action_id = 'index';

	 ca_update_language_set();
	 $ca_lang_template = Detector::defineSourcePage($ca_pref, $ca_lang, (in_array($ca_action_id, $ca_user_actions) || in_array($ca_action_id, $ca_myprofile_actions) ? true : '')); // needed here in order to define $f->mobile_detected

	 if ((in_array($ca_action_id, $ca_user_actions) || in_array($ca_action_id, $ca_myprofile_actions)) && strpos($ca_lang_template, '/') !== false)
		  $ca_pref_dir = '../documents/';

	 if (in_array($ca_action_id, $ca_myprofile_actions) && $user->getUserCookie() == '')
		  $ca_action_id = 'index';
	 else
		  Session::intStart('private');

	 if (isset($ca_loggedcheck) && ($ca_loggedcheck == true)) {
		  if (Cookie::isAdmin())
				$ca_logged_access = array_keys($ca_areaarray);
		  elseif ($user->userCookie()) {
				$user_account = $user->getUser($user->getUserCookie(), $ca_pref);
				if ($user_account['access'][0]['section'] == 'ALL') {
					 $ca_logged_access = array_keys($ca_areaarray);
				} else {
					 foreach ($user_account['access'] as $k => $v)
						  $ca_logged_access[] = $v['section'];
				}
		  } else
				$ca_logged_access = array();
	 }
	 elseif ($ca_action_id == 'logout' || $ca_action_id == "logoutadmin")
		  logout_user();
	 elseif ($ca_action_id == "version") {
		  echo $version . ' ' . $f->version;
	 } elseif ($ca_action_id == "next" || $ca_action_id == "prev") {
		  $all_pages = get_pages_list();
		  $new_page = '';
		  foreach ($all_pages as $k => $v) {
				if (isset($v['pageid']) && $v['pageid'] == $_REQUEST['id']) {
					 $c_lang = $v['lang'];
					 $orig_page = $v['url'];
					 $new_i = ($ca_action_id == "next" ? $k + 1 : $k - 1);

					 if (isset($all_pages[$new_i]['pageid'])) {
						  if ($all_pages[$new_i]['hidden'] == 'FALSE' && $all_pages[$new_i]['lang'] == $c_lang)
								$new_page = $all_pages[$new_i]['url'];
						  elseif ($all_pages[$new_i]['lang'] == $c_lang) {
								while (!isset($all_pages[$new_i]['hidden']) || $all_pages[$new_i]['hidden'] == 'TRUE') {
									 if ($ca_action_id == "next")
										  $new_i++;
									 else
										  $new_i--;
								}
								if ($all_pages[$new_i]['hidden'] == 'FALSE' && $all_pages[$new_i]['lang'] == $c_lang) {
									 $new_page = $all_pages[$new_i]['url'];
								}
						  }
					 }
				}
		  }
		  if (empty($new_page))
				$new_page = $orig_page;
		  $new_page = (strpos($new_page, '../') === false ? '../' : '') . $new_page;
		  Linker::redirect($new_page, false);
		  exit;
	 }
	 elseif ($ca_action_id == "register" || $ca_action_id == "register2")
		  process_register($ca_action_id == "register2");
	 elseif ($ca_action_id == "loggedinfo" || $ca_action_id == "loggeduser" || $ca_action_id == "logged") {
		  if (!isset($_SERVER['HTTP_REFERER'])) {
				Linker::redirect("centraladmin.php?process=index", false);
				exit;
		  } else {
				if ($ca_action_id == "loggedinfo")
					 $logged_info = user_navigation(false, true);
				elseif ($ca_action_id == "logged") {
					 $logged_info = user_navigation_float(true);
					 echo json_encode($logged_info);
					 exit;
				} else
					 $logged_info = user_navigation(true, true);
				$out = isset($_REQUEST['nodw']) ? $logged_info : "\ndocument.write(' $logged_info ');\n";
				echo $out;
		  }
	 }
	 elseif ($ca_action_id == "forgotpass" || $ca_action_id == "forgotpass2")
		  process_forgotpass();
	 elseif ($ca_action_id == 'sitemap') {
		  $fc = (isset($_GET['pwd']) && crypt($_GET['pwd'], 'admin') == 'adPTFL0iJCHec') ? File::read($ca_pref . 'sitemap.php') : '';
		  print str_replace(array('<?php echo "hi"; exit; /*', '*/ ?>'), array('', ''), $fc);
		  exit;
	 } elseif (in_array($ca_action_id, $ca_admin_actions)) {
		  $table_data = array();
		  $end = $output = '';
		  if ($user->isEZGAdminNotLogged()) {
				if (strpos($ca_lang_template, '/') !== false)
					 $ca_pref_dir = '../documents/';
				login_admin();
				exit;
		  }
		  if ($ca_action_id == "index")
				index();
		  elseif ($ca_action_id == "manageusers")
				manage_users();
		  elseif ($ca_action_id == "processuser")
				process_users();
		  elseif ($ca_action_id == "pendingreg")
				check_pending_users();
		  elseif ($ca_action_id == "confcounter")
				conf_counter();
		  elseif ($ca_action_id == "toggleFold")
				CA::setCaMiniCookie(isset($_REQUEST['fold']) && ($_REQUEST['fold'] == '1'));
		  elseif ($ca_action_id == "resetcounter") {
				if (isset($_GET['confirmreset']) && file_exists($counter_ts_db_fname) && (filesize($counter_ts_db_fname) !== 0)) {
					 $files = array($counter_ts_db_fname, $counter_ds_db_fname);
					 foreach ($files as $k => $v) {
						  $fp = fopen($v, 'r+');
						  flock($fp, LOCK_EX);
						  ftruncate($fp, 0);
						  fseek($fp, 0);
						  flock($fp, LOCK_UN);
						  fclose($fp);
					 }
					 File::writeTaggedData("counter_cookie_suffix", time(), $ca_db_settings_file, $ca_template_file);
					 clearstatcache();
					 $table_data[] = array('', '<span class="rvts8">' . $ca_lang_l['reset done'] . '</span>');
					 $flag = true;
				} else {
					 $table_data[] = array('', '<span class="rvts8">' . $ca_lang_l['reset MSG1'] . '</span>');
					 $end = '<input type="button" value=" ' . $ca_lang_l['confirm counter reset'] . ' " onclick="document.location=\'' . Linker::buildSelfURL('centraladmin.php') . '?process=resetcounter&amp;confirmreset=confirm' . $ca_l_amp . '\'" onclick="javascript:return confirm(\'' . $ca_lang_l['reset MSG2'] . '\')">';
					 $flag = false;
				}
				$output.=Builder::addEntryTable($table_data, $end);
				$output = Formatter::fmtAdminScreen($output, build_menu(' - ' . $ca_lang_l['reset counter']));
				print GT($output, $flag);
		  } elseif ($ca_action_id == "confreg")
				conf_registration();
		  elseif ($ca_action_id == "confreglang") {
				$abs_url = Linker::buildSelfURL('centraladmin.php');
				$cur_lang = (isset($_GET['sr_lang']) ? $_GET['sr_lang'] : 'EN');
				if (isset($_POST['submit'])) {
					 $post_lang = $_POST['language'];
					 $record_line = '';
					 foreach ($ca_reg_lang_settings_keys as $k => $v) {
						  if ($v == 'repeat password' || $v == 'want to receive notification')
								$setting_value = $_POST[str_replace(' ', '_', $v)];
						  else
								$setting_value = (isset($_POST[$v])) ? str_replace(F_LF, '##', Formatter::esc(trim($_POST[$v]))) : '';
						  $record_line.='<' . $v . '>' . $setting_value . '</' . $v . '>';
					 }
					 if (!empty($record_line))
						  File::writeTaggedData("sr_language_" . $post_lang, $record_line, $ca_db_settings_file, $ca_template_file);
					 $table_data[] = array('', '<span class="rvts8">' . $ca_lang_l['settings saved'] . '</span>');
					 $f->ca_settings = File::read($ca_db_settings_file);
					 ca_update_language_set();
				}
				else {
					 $lang_set_sr = File::readLangSet($ca_lang_set_fname, $cur_lang, 'ca');
					 $sr_lang_l = (isset($lang_set_sr['lang_l'])) ? $lang_set_sr['lang_l'] : $ca_lang_l;
					 $reg_lang_set_raw = Formatter::GFS($f->ca_settings, '<sr_language_' . $cur_lang . '>', '</sr_language_' . $cur_lang . '>');
					 if ($reg_lang_set_raw != '') {
						  foreach ($ca_reg_lang_settings_keys as $k => $v) {
								if (strpos($reg_lang_set_raw, '<' . $v . '>') !== false)
									 $sr_lang_l[$v] = Formatter::unEsc(Formatter::GFS($reg_lang_set_raw, '<' . $v . '>', '</' . $v . '>'));
						  }
					 }

					 $input = '<input class="input1" type="text" name="%s" value="%s" style="width:500px" maxlength="250">';
					 $area = '<textarea class="input1" name="%s" cols="35" rows="7" style="width:500px">%s</textarea>';
					 $jstring = 'onchange="document.location=\'' . ($template_in_root ? $abs_url : 'centraladmin.php') . '?process=confreglang&amp;sr_lang=\' + this.options[this.selectedIndex].value;"';

					 $table_data[] = array($ca_lang_l['edit_language'], Builder::buildSelect("language", $f->names_lang_sets, $cur_lang, '', 'key', $jstring));
					 foreach ($ca_reg_lang_settings_keys as $k => $v) {
						  if (array_key_exists($v, $sr_lang_l)) {
								$label = $ca_reg_lang_settings_labels[$k];
								$setting_value = str_replace('##', F_LF, Formatter::sth($sr_lang_l[$v]));
								if ($v == 'sr_email_msg' || $v == 'sr_forgotpass_msg' || $v == 'sr_forgotpass_msg0' || $v == 'sr_activated_msg' || $v == 'sr_blocked_msg')
									 $table_data[] = array($label, sprintf($area, $v, $setting_value));
								else
									 $table_data[] = array($label, sprintf($input, $v, $setting_value));
						  }
					 }
					 $end = ca_getformbuttons('submit') . F_BR;
				}
				$output = $f->navtop . '<input type="button" value=" ' . $ca_lang_l['settings'] . ' " onclick="document.location=\'' . $abs_url . '?process=confreg\'"> <input type="button" value=" ' . $ca_lang_l['language'] . ' " onclick="document.location=\'' . $abs_url . '?process=confreglang\'">' . $f->navend . F_BR;
				$output.='<div style="text-align:left"><form method="post" action="' . $ca_pref_dir . 'centraladmin.php?process=confreglang">';
				$output.=Builder::addEntryTable($table_data, $end) . '</form></div>';
				$output = Formatter::fmtAdminScreen($output, build_menu(' - ' . $ca_lang_l['language']));
				$output = GT($output);
				if (!isset($_POST['submit'])) {
					 $charset = Formatter::GFS($output, 'charset=', '"');
					 $new_charset = (strpos(Formatter::strToLower($charset), 'utf') !== false) ? 'UTF-8' : $f->charset_lang_map[$cur_lang];
					 if ($charset != '')
						  $output = str_replace('charset=' . $charset . '"', 'charset=' . $new_charset . '"', $output);
				}
				print $output;
		  }
		  elseif ($ca_action_id == "conflang") {
				$logout_redirect_url = Formatter::GFS($f->ca_settings, '<logout_redirect_url>', '</logout_redirect_url>');
				$tzone_offset = Formatter::GFS($f->ca_settings, '<tzoneoffset>', '</tzoneoffset>');
				$lang_set = Formatter::GFS($f->ca_settings, '<language>', '</language>');

				if (isset($_POST['submit'])) {
					 setcookie('ca_lang', strtoupper(Formatter::stripTags($_POST['lang'])), mktime(23, 59, 59, 1, 1, 2037), str_replace('http://' . Linker::getHost(), '', $ca_site_url));
					 File::writeTaggedData(array('language', 'logout_redirect_url', 'tzoneoffset'), array($_POST['lang'], $_POST['logout_redirect_url'], $_POST['tzone_offset']), $ca_db_settings_file, $ca_template_file);
					 $table_data[] = array('', '<span class="rvts8">' . $ca_lang_l['settings saved'] . '</span>');
					 $f->ca_settings = File::read($ca_db_settings_file);
					 ca_update_language_set(strtoupper(Formatter::stripTags($_POST['lang'])));
				} else {
					 $table_data[] = array($ca_lang_l['language'], Builder::buildSelect('lang', $f->names_lang_sets, strtoupper($lang_set)));
					 $table_data[] = array($ca_lang_l['set tzone'], "<input class='input1' name='tzone_offset' type='text' value='" . $tzone_offset . "' size='3'>");
					 $table_data[] = array($ca_lang_l['redirect page'], "<input class='input1' type='text' name='logout_redirect_url' style='width:350px' value='" . $logout_redirect_url . "'>" . F_BR . F_BR . CA::formatNotice($ca_lang_l['redirect page msg']));
					 $end = ca_getformbuttons('submit') . F_BR;
				}

				if ($f->uni && !function_exists('mb_strtolower'))
					 $table_data[] = array('Multibyte Library', F_BR . '<span class="rvts8">Disabled!</span>');
				if (!function_exists('imagecreatetruecolor'))
					 $table_data[] = array('GD Library', F_BR . '<span class="rvts8">Disabled!</span>');
				if (!function_exists("iconv"))
					 $table_data[] = array('Iconv Library', F_BR . '<span class="rvts8">Disabled!</span>');

				$output = '<form action="' . $ca_pref_dir . 'centraladmin.php?process=conflang" method="post"><div style="text-align:left">';
				$output.=Builder::addEntryTable($table_data, $end) . "</div></form>";
				$output = Formatter::fmtAdminScreen($output, build_menu());
				print GT($output);
		  }
		  elseif ($ca_action_id == "log") {
				$logcontent = array();
				clearstatcache();
				if (file_exists($ca_db_activity_log)) {
					 $handle = fopen($ca_db_activity_log, 'r');
					 while ($data = fgetcsv($handle, 8192, '^')) {
						  if ($data[0] != "<?php echo 'hi'; exit; /*") {
								$ip = '';
								$sep_cnt = substr_count($data[0], '=>');
								if ($sep_cnt == 3)
									 list($dt, $temp, $result, $ip) = explode('=>', $data[0]);
								elseif ($sep_cnt == 2)
									 list($dt, $temp, $result) = explode('=>', $data[0]);
								else
									 continue;	//error in db

								list($activity, $username) = explode('->', $temp);
								if (strpos($username, 'EMAIL:') !== false)
									 $username = Formatter::GFS($username, 'USER:', 'EMAIL:');
								elseif (strpos($username, 'ID:') !== false)
									 $username = Formatter::GFS($username, 'USER:', 'ID:');
								else
									 $username = str_replace('USER:', '', $username);

								$logcontent[] = array('date' => trim($dt), 'activity' => trim($activity), 'user' => str_replace(F_LF, F_BR, urldecode($username)) . ' ' . ($ip != '' ? Builder::ipLocator($ip) : ''), 'result' => str_replace('Result:', '', $result));
						  }
					 }
					 fclose($handle);
				}
				$output = '';
				if (!empty($logcontent)) {
					 $logcontent = array_reverse($logcontent);
					 $records_count = count($logcontent);
					 $screen = (isset($_GET['page']) ? $_GET['page'] : 1);
					 $offset = ($screen == 1) ? 0 : ($screen - 1) * Navigation::recordsPerPage();
					 $limit_rec_to = ($screen * Navigation::recordsPerPage() > $records_count) ? Navigation::recordsPerPage() - ($screen * Navigation::recordsPerPage() - $records_count) : Navigation::recordsPerPage();
					 $show_records = array_slice($logcontent, $offset, $limit_rec_to);

					 $url_part = $ca_pref_dir . "centraladmin.php?process=log";
					 $nav = Navigation::pageCA($records_count, $url_part, 0, $screen);
					 $cap_arrays = array($ca_lang_l['date'], $ca_lang_l['activity'], $ca_lang_l['user'], $ca_lang_l['result']);
					 $table_data = array();

					 foreach ($show_records as $key => $value) {
						  if (!empty($value)) {
								if (intval($value['date']) > 0)
									 $date_value = date('d M Y h:i:s', Date::tzone($value['date']));
								else
									 $date_value = $value['date'];

								$row_data = array($ca_span8 . $date_value . "</span>", $ca_span8 . " :: " . $value['activity'] . "</span>", $ca_span8 . $value['user'] . "</span>", $ca_span8 . " :: " . $value['result'] . "</span>");
								$table_data[] = $row_data;
						  }
					 }
					 $append = '<form method="post" action="' . $ca_pref_dir . 'centraladmin.php?process=clearlog' . $ca_l_amp . '">'
								. '<input type="submit" value=" ' . $ca_lang_l['clear log'] . ' " onclick="javascript:return confirm(\'' . $ca_lang_l['clear log MSG'] . '\')"></form>';
					 $output.=Builder::adminTable($nav, $cap_arrays, $table_data, $append);
				}
				else {
					 $table_data[] = array('', '<span class="rvts8">Empty</span>');
					 $output = Builder::addEntryTable($table_data, $end);
				}
				$output = Formatter::fmtAdminScreen($output, build_menu());
				print GT($output);
		  } elseif ($ca_action_id == "clearlog") {
				if (!$handle = fopen($ca_db_activity_log, 'r+')) {
					 print Formatter::fmtInTemplate($ca_template_file, Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD', $ca_db_activity_log));
					 exit;
				}
				ftruncate($handle, 0);
				fseek($handle, 0);
				fclose($handle);
				$table_data[] = array('', '<span class="rvts8">' . $ca_lang_l['log file cleared'] . '</span>');
				$output = Builder::addEntryTable($table_data, $end);
				$output = Formatter::fmtAdminScreen($output, build_menu());
				print GT($output);
		  } elseif ($ca_action_id == "export") {
				$output = '';
				$users = db_get_users();
				if ($users != '') {
					 $users_array = $user->formatUsers($users);
				} else {
					 $users_array = array();
				}
				if (count($users_array) > 1) {
					 foreach ($users_array as $key => $row) {
						  $name[$key] = $row['username'];
					 }
					 $name_lower = array_map('strtolower', $name);
					 array_multisort($name_lower, SORT_ASC, $users_array);
				}
				if (!empty($users_array)) {
					 $field_names = array('username', 'name', 'sirname', 'email', 'creation_date', 'self-registered');
					 foreach ($field_names as $k => $v) {
						  $output.=($k == 0 ? '' : ',') . '"' . Formatter::sth(urldecode($v)) . '"';
					 }
					 $output.=F_LF;

					 foreach ($users_array as $key => $value) {
						  $output.='"' . Formatter::sth(urldecode($value['username'])) . '"';
						  $output.=',"' . un_esc(urldecode($value['first_name'])) . '"';
						  $output.=',"' . un_esc(urldecode($value['surname'])) . '"';
						  $output.=',"' . Formatter::sth(urldecode($value['email'])) . '"';
						  $output.=',"' . $value['creation_date'] . '"';
						  $output.=',"' . (isset($value['self_registered']) && $value['self_registered'] == '1' ? 'Yes' : 'No') . '"';
						  $output.=',"' . (isset($value['status']) && $value['status'] == '1' ? 'Active' : 'Blocked') . '"';
						  $output.=F_LF;
					 }
				}
				output_generator::sendFileHeaders('users_export.csv');
				print $output;
				exit;
		  }
	 } elseif (in_array($ca_action_id, $ca_myprofile_actions)) { // my profile
		  $output = '';
		  $username = $user->getUserCookie();
		  $access_myprofile = $username != '';
		  if ($access_myprofile) {
				$user_data = $user->getUser($username, $ca_pref);

				if (isset($_GET['setlang'])) {
					 setcookie($username . '_lang', strtoupper(Formatter::stripTags($_GET['setlang'])), mktime(23, 59, 59, 1, 1, 2037), str_replace('http://' . Linker::getHost(), '', $ca_site_url));
					 ca_update_language_set(strtoupper(Formatter::stripTags($_GET['setlang'])));
				}

				if ($ca_action_id == "changepass") {
					 process_changepass();
					 exit;
				} elseif ($ca_action_id == "editprofile") {
					 process_editprofile();
					 exit;
				} elseif ($ca_action_id == "myprofile") {
					 $all_edit_access = (isset($user_data['access']) && $user_data['access'][0]['section'] == 'ALL' && $user_data['access'][0]['type'] == '1');
					 $all_view_access = (isset($user_data['access']) && $user_data['access'][0]['section'] == 'ALL' && $user_data['access'][0]['type'] == '0');

					 $pages_list = get_pages_list();
					 $cap_arrays = array($ca_lang_l['page name'], $ca_lang_l['admin link']);

					 $lang_flag = '';
					 foreach ($pages_list as $k => $v) {
						  $edit_flag = $all_edit_access;
						  $view_flag = ($all_edit_access || $all_view_access);

						  $page_text = '';
						  $admin_text = '';
						  if (!empty($v['name']) && !isset($v['id']) && isset($pages_list[$k + 1]['lang']) && $pages_list[$k + 1]['lang'] != $lang_flag) {
								$row_data = '<span class="a_lang_label">' . $pages_list[$k + 1]['lang'] . '</span>';
								$table_data[] = $row_data;
								$lang_flag = $pages_list[$k + 1]['lang'];
						  }

						  foreach ($user_data['access'] as $u_access_v) {
								$is_sec = (isset($v['section']) && $v['section'] == $u_access_v['section']);
								if (isset($v['section']) && ($is_sec || !$f->use_prot_areas)) {
									 if ($is_sec && $u_access_v['type'] == '0')
										  $view_flag = true;
									 elseif ($is_sec && $u_access_v['type'] == '1') {
										  $edit_flag = true;
										  $view_flag = true;
									 } elseif ($u_access_v['type'] == '2' && isset($u_access_v['page_access'])) {
										  foreach ($u_access_v['page_access'] as $key => $val) {
												if ($v['pageid'] == $val['page']) {
													 if ($val['type'] == '1' || $val['type'] == '3') {
														  $edit_flag = true;
														  $view_flag = true;
													 } elseif ($val['type'] == '0')
														  $view_flag = true;
													 break;
												}
										  }
									 }
									 if ($is_sec)
										  break;
								}
						  }

						  if (count($v) == 1) {
								$cnt = 0;
								$cat = $v['name'];
						  }

						  if (isset($v['id'])) {
								$key_sf = array_search($v['pageid'], $forms);
								if ($key_sf !== false) {
									 $v['adminurl'] = (strpos($v['url'], '../') !== false ? Formatter::GFSAbi($v['url'], '../', '/') : '') . 'newsletter_' . $key_sf . '.php?action=index';
								}

								$ca_ctrl = (in_array($v['id'], $f->sp_pages_ids) || $v['editable'] == 'TRUE' || $key_sf !== false);
								if ($ca_ctrl && $edit_flag || $v['protected'] == 'TRUE' && $view_flag) {
									 if ($cnt == 0) {
										  $row_data = '<span class="a_lang_label">' . $cat . '</span>';
										  $table_data[] = $row_data;
									 }

									 if ($template_in_root) {
										  $v_url = str_replace('../', '', $v['url']);
										  $supage_url = str_replace('../', '', $v['subpage_url']);
									 } else {
										  $v_url = (strpos($v['url'], '../') === false ? '../' : '') . $v['url'];
										  $supage_url = (strpos($v['subpage_url'], '../' === false) ? '../' : '') . $v['subpage_url'];
									 }
									 $page_text.=$ca_span8;
									 if ($v['subpage'] == '1')
										  $page_text.='&nbsp;&nbsp;&nbsp;&nbsp;- </span><a target="_blank" class="rvts8 nodec"  href="' . $v_url . '">';
									 else
										  $page_text.=':: </span><a class="rvts8 nodec" href="' . $v_url . '">';
									 $page_text.=$v['name'] . '</a>';

									 if ($edit_flag && $ca_ctrl) {
										  if ($template_in_root)
												$admin_url = str_replace('../', '', $v['adminurl']);
										  else
												$admin_url = (strpos($v['adminurl'], '../') === false) ? '../' . $v['adminurl'] : $v['adminurl'];
										  $admin_text.=$ca_span8 . "[</span><a class='rvts12' href='" . $admin_url . $ca_l . "'>";
										  $admin_text.=$ca_lang_l['edit'] . "</a>" . $ca_span8 . "]</span>";
									 }
									 $row_data = array($page_text, $admin_text);
									 $table_data[] = $row_data;
									 $cnt++;
								}
						  }
					 }
				}
				$output = Builder::adminTable('', $cap_arrays, $table_data);
		  }
		  $output = Formatter::fmtAdminScreen($output, build_myprofile_menu());
		  print GT($output, false, '', true);
		  exit;
	 }
	 else {
		  if (empty($_POST) && empty($thispage_id) && !isset($_GET['pageid'])) {
				Linker::redirect("centraladmin.php?process=index", false);
				exit;
		  }

		  $ca_miniform = (isset($_GET['pageid']) && !isset($_POST['loginid']) && !isset($_GET['indexflag']) && !isset($_GET['ref_url']) && !empty($_POST));
		  $float_login = isset($_POST['redirect_to']);

		  //are we trying to login?
		  $wewantlogin = (isset($_POST['pv_username']) && isset($_POST['pv_password']));
		  if ($wewantlogin) {
				$pv_username = trim(Formatter::stripTags($_POST['pv_username']));
				$pv_password = trim($_POST['pv_password']);
				$pass_filled = md5($pv_password);
				if (strtolower($ca_admin_username) == 'admin' && strtolower($ca_admin_username) == strtolower($pv_username) && ($ca_admin_pwd == md5('admin') ||
						  $ca_admin_pwd == md5('Admin') || $ca_admin_pwd == md5('ADMIN')) && ($ca_admin_pwd == md5(strtolower($pv_password)) ||
						  $ca_admin_pwd == md5(ucfirst($pv_password)) || $ca_admin_pwd == md5(strtoupper($pv_password)))) {
					 print GT($ca_account_msg);
					 exit;
				}
				$isitadmin = ($pv_username == $ca_admin_username);
		  } else
				$isitadmin = Cookie::isAdmin();

		  if (isset($_GET['pageid']) && isset($_POST['loginid']) || $ca_miniform) { // when login page or miniform is directly accessed
				$cur_section = (isset($_POST['loginid'])) ? Formatter::stripTags($_POST['loginid']) : '';
				if (!isset($pv_username) || !isset($pv_password) || $pv_username == '' || $pv_password == '')
					 error('1', false);

				if (intval($_GET['pageid']) == 0 && $thispage_id == "0") {	 //login page accessed directly, not from admin link
					 $controlled_pages = get_prot_pages_list($cur_section);
					 $protected_pages = array();
					 $special_ids = array();
					 foreach ($controlled_pages as $k => $v) {
						  if ($v['protected'] == 'TRUE' || in_array($v['typeid'], $f->sp_pages_ids))
								$protected_pages[] = $v['id'];
						  if (in_array($v['typeid'], $f->sp_pages_ids))
								$special_ids[] = $v['id'];
					 }

					 if (empty($protected_pages) && isset($_POST['loginid'])) { // for login pages - will be removed later
						  $controlled_pages = get_prot_pages_list();
						  foreach ($controlled_pages as $k => $v) {
								if ($v['protected'] == 'TRUE' || in_array($v['typeid'], $f->sp_pages_ids))
									 $protected_pages[] = $v['id'];
								if (in_array($v['typeid'], $f->sp_pages_ids))
									 $special_ids[] = $v['id'];
						  }
					 }

					 $redirect_to_page = '';
					 $user_account = $user->getUser($pv_username, $ca_pref);

					 if ($pv_username == $ca_admin_username && $ca_admin_pwd == $pass_filled)
						  $redirect_to_page = (isset($protected_pages[0])) ? $protected_pages[0] : 'admin';
					 elseif (empty($user_account))
						  error('2', true, $user_account);
					 else {
						  if ($user_account['username'] == $pv_username && $user_account['password'] == crypt($pv_password, $user_account['password'])) {
								if ($user_account['access'][0]['section'] != 'ALL') {
									 foreach ($user_account['access'] as $k => $v) {
										  if ($cur_section == $v['section'] || $_GET['pageid'] == "0" || !$f->use_prot_areas) {
												if ($v['type'] != '2') {
													 foreach ($controlled_pages as $vvv) {
														  if (($vvv['protected'] == 'TRUE' || in_array($vvv['typeid'], $f->sp_pages_ids)) && $vvv['section'] == $v['section']) {
																$redirect_to_page = $vvv['id'];
																break;
														  }
													 }
												} elseif (isset($v['page_access'])) {
													 foreach ($v['page_access'] as $key => $val) {
														  if (in_array($val['page'], $protected_pages)) {
																if (($val['type'] == '1' && in_array($val['page'], $special_ids)) || ($val['type'] == '0' && !(in_array($val['page'], $special_ids)))) {
																	 $redirect_to_page = $val['page'];
																	 break;
																}
														  }
													 }
												}
										  }
									 }
								} elseif (isset($protected_pages[0]))
									 $redirect_to_page = $protected_pages[0];
						  } else
								error('3', true, $user_account);
					 }

					 if (empty($redirect_to_page)) {
						  print GT(F_BR . "<span class='rvts8'><b>The system doesn't know where to redirect you.</b></span>");
						  exit;
					 } else {
						  $prot_page_info = get_page_info($redirect_to_page);
						  $thispage_id = str_replace('<id>', '', trim($prot_page_info[10]));
					 }
				}

				if (!isset($pv_username) || !isset($pv_password) || $pv_username == '' || $pv_password == '')
					 error('4', false);
				else {
					 $prot_page_info = get_page_info($thispage_id);
					 $user_account = $user->getUser($pv_username, $ca_pref);
					 if (Validator::checkProtection($prot_page_info) > 1 && f_has_read_access($user_account, $prot_page_info) == false) {
						  if ($ca_admin_username != $pv_username || $ca_admin_pwd != $pass_filled) {
								error('5', true, $user_account);
						  }
					 }
				}
		  }
		  $prot_page_info = get_page_info($thispage_id);
		  $prot_page_name = $prot_page_info[1];

		  if ($rss_call_in_prot_page && in_array($prot_page_info[4], array('136', '137', '138', '143', '144'))) { // public rss when page is protected
				$rss_settings_dir = $ca_pref . $f->db_folder;
				if ($prot_page_info[4] == '144')
					 $rss_public_on = File::read($rss_settings_dir . $thispage_id . "_db_guestbook.ezg.php");
				elseif ($prot_page_info[4] == '136')
					 $rss_public_on = File::read($rss_settings_dir . $thispage_id . "_settings.ezg.php");
				else
					 $rss_public_on = File::read($rss_settings_dir . $thispage_id . "_blocked_ips.ezg.php");
				$rss_public_on = Formatter::GFS($rss_public_on, '<public_rss>', '</public_rss>');
		  }

		  //start of actual pwd protection check
		  if (isset($rss_public_on) && $rss_public_on == '1')
				$access_flag = true;
		  elseif ($user->isEZGAdminNotLogged()) {
				if ($user->userCookie())
					 $user_account = $user->getUser($user->getUserCookie(), $ca_pref);
				if ((!$user->userCookie()) || f_has_read_access($user_account, $prot_page_info) == false) {
					 if (!isset($pv_username) && !isset($pv_password)) {
						  if (strtolower($ca_admin_username) == 'admin' && ($ca_admin_pwd == md5('admin') || $ca_admin_pwd == md5('Admin') || $ca_admin_pwd == md5('ADMIN'))) {
								print GT($ca_account_msg);
								exit;
						  }
						  $ref_url = (isset($_GET['ref_url'])) ? Formatter::stripTags($_GET['ref_url']) : '';
						  if (!isset($user_account))
								$user_account = array();
						  $contents = build_login_form('', $ref_url, $user_account);
						  print $contents;
						  exit;
					 }
					 elseif (!isset($pv_username) || !isset($pv_password) || $pv_username == '' || $pv_password == '')
						  error('6', false);
					 elseif ($isitadmin) { //is it admin?
						  if ($pass_filled == $ca_admin_pwd) {
								Session::regenerateID();
								Cookie::setAdmin($ca_admin_username);
								write_log('login', 'USER:Administrator', 'success');
								if (isset($_SERVER['HTTP_USER_AGENT']))
									 Session::setVar('HTTP_USER_AGENT', md5($_SERVER['HTTP_USER_AGENT']));
								set_admin_cookie(); // for counter - to ignore hits from site admin
								$access_flag = true;
						  }
						  else { //wrong username or password
								if (!isset($user_account))
									 $user_account = array();
								error('7', true, $user_account);
						  }
					 }
					 else { //user
						  $user_account = $user->getUser($pv_username, $ca_pref);
						  if (empty($user_account))
								error('8', true, $user_account);
						  else {
								if ($user_account['status'] != '1')
									 error('12', true, $user_account);
								else {
									 $log_check = $user_account['password'] == crypt($pv_password, $user_account['password']);

									 if ($log_check) {
										  Session::regenerateID();
										  $user->setUserCookie($user_account['username']);
										  write_log('login', 'USER:' . $pv_username, 'success');
										  $access_flag = true;
									 } else
										  error('8', true, $user_account);  //wrong username or password
								}
						  }
					 }
				} else
					 $access_flag = true;
		  } else
				$access_flag = true;  //end of actual pwd protection check

		  if (isset($_REQUEST['pv_username']) && isset($_POST['cc']) && $_POST['cc'] == '1') {
				print '1';
				exit;
		  }

		  if (isset($_GET['pageid']) && $access_flag) {
				if ($access_flag == true) {
					 $load_page = $prot_page_name;
					 if (isset($_GET['indexflag']) || Validator::checkProtection($prot_page_info) == 1) {
						  $username = isset($pv_username) ? $pv_username : (isset($user_account['username']) ? $user_account['username'] : '');
						  $writeaccess_flag = $isitadmin || $user->hasWriteAccess($username, $prot_page_info, $ca_pref);
						  if (!$writeaccess_flag) {
								if ($float_login)
									 $load_page = $prot_page_name;
								else
									 $load_page = (strpos($prot_page_name, '../') === false ? 'documents/' : '') . 'centraladmin.php?process=myprofile';
						  }
						  elseif ($prot_page_info[4] == '143' && strpos($prot_page_info[1], '?flag=podcast') !== false)
								$load_page = $prot_page_name . '&action=index';
						  elseif ($prot_page_info[4] == '133') {
								$dir = (strpos($prot_page_info[1], '../') === false) ? '' : '../' . Formatter::GFS($prot_page_info[1], '../', '/') . '/';
								$load_page = $dir . 'newsletter_' . str_replace('<id>', '', $prot_page_info[10]) . '.php?action=subscribers';
						  } elseif ($prot_page_info[4] == '20') {
								if (Session::isSessionSet('cur_pwd' . intval($_GET['pageid'])))
									 $r_with = 'action=remcookie';
								else
									 $r_with = 'action=doedit';
								if (strpos($prot_page_name, 'action=show') !== false)
									 $load_page = str_replace('action=show', $r_with, $prot_page_name);
								else
									 $load_page = $prot_page_name . '?' . $r_with;
						  } else
								$load_page = $prot_page_name . '?action=index';
					 }

					 if (isset($redirect_to_page) && $redirect_to_page == 'admin')
						  $load_page = '../documents/centraladmin.php?process=index';

					 if (isset($_GET['ref_url']))
						  $load_page = Formatter::stripTags($_GET['ref_url']); //event manager
					 if (strpos($prot_page_name, '../') === false) {
						  $load_page = '../' . $load_page;
					 }

					 Linker::redirect($load_page, false);
					 exit;
				}
		  }
	 }
}

process_admin();
?>
