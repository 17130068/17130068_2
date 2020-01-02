<?php
$version='ezgenerator v4 - search 5.2.';
/*
	search.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2015 Image-line
*/
$AccChars=array("á","à","â","ã","à","Á","À","Â","Ã","À","ç","Ç","ð","é","è","ê","É","È","Ê","í","ì","î","Í","Ì","Î","ó","ò","ô","Ó","Ò","Ô","‘","Š","ú","ù","û","Ú","Ù","Û","ý","Ý","ž","Ž","ä","Ä","ë","Ë","ï","Ï","ö","Ö","ü","Ü","ÿ","Ÿ","å","Å","ø","Ø","æ","Æ","œ","Œ","ß");
$NormChars=array("a","a","a","a","a","A","A","A","A","A","c","C","d","e","e","e","E","E","E","i","i","i","I","I","I","o","o","o","O","O","O","s","S","u","u","u","U","U","U","y","Y","z","Z","a","A","e","E","i","I","o","O","u","U","y","Y","aa","Aa","oe","Oe","ae","AE","oe","OE","ss");

$s_rel_path=(isset($rel_path)?$rel_path:'../');
include_once ($s_rel_path.'ezg_data/functions.php');
$s_php_pages_ids=array('20','133','136','137','138','143','144','181','190');
$s_gt_page=Detector::defineSourcePage();
$self_url=Linker::buildSelfURL('search.php');
$s_full_path_to_script=substr($self_url,0,strrpos($self_url,"/"));
$s_db_dir=$s_rel_path.$f->db_folder;
$s_index_template_areas=true;
$s_page_id=(isset($_REQUEST['id'])? intval($_REQUEST['id']): '0'); $lang='EN';
if($s_page_id>0)
{$pageid_info=CA::getPageParams($s_page_id); if(isset($pageid_info[16])) $lang=$f->inter_languages_a[array_search($pageid_info[16],$f->site_languages_a)];}
$s_lang_settings_all=array("0"=>array("all these words"=>"all these words","Result"=>"Result","Page"=>"Page","From"=>"From","first"=>"first","prev"=>"prev","next"=>"next","last"=>"last","no matches found"=>"No matches found","Search"=>"Search","search box empty"=>"Search box is empty. Please, type search keyword in it.","page created in"=>"Page generated in","seconds"=>"seconds","last modified"=>"Last modified","find pages that"=>"Find web pages that have","this exact wording"=>"this exact wording or phrase","one or more"=>"one or more of these words","search in"=>"Search in","whole site"=>"whole site","only in"=>"only in","current page"=>"current page","by last modified date"=>"Search by last modified date","anytime"=>"anytime","past 24 hours"=>"past 24 hours","past week"=>"past week","past month"=>"past month","past year"=>"past year","pages updated"=>"pages updated","advanced search"=>"advanced search","search again"=>"search again","load more"=>"load more","created date"=>"Created","search by date"=>"Search by date"));
$s_lang_settings=$s_lang_settings_all[array_search($lang, $f->inter_languages_a)];
$showresulttime=false;

if(!empty($f->search_templates_a))
{
	$s_sitemap=CA::getSitemap($s_rel_path,false,true);
	if($f->search_templates_a[0]!='0')
	{
		$ss=$f->search_templates_a[0];
		if(isset($s_sitemap[$ss])) $s_gt_page=$s_sitemap[$ss][1];
	}
	foreach($f->search_templates_a as $k=>$v)
	{
		$ss=intval($v)>0?intval($v):0;
		if($ss>0)
		{
		 if(isset($s_sitemap[$ss]) && $s_sitemap[$ss]['22']==$lang) $s_gt_page=$s_sitemap[$ss][1];
		}
	}
}

function s_GTs($template_content, $result_output,$query='',$id='',$page_charset='')
{
	global $f,$s_full_path_to_script,$s_gt_page;

	$search_part='';
	$indir=(strpos($s_gt_page, '../')===false);

	if(!empty($id))
	{
		if($page_charset!=='') $template_content=str_replace(Formatter::GFS($template_content,'charset=','"'),$page_charset,$template_content);
		if(strpos($template_content,'<!--search-->')!==false)
		{
			$search_part=F_BR.Formatter::GFS($template_content,'<!--search-->','<!--/search-->');
			$search_part=str_replace('name="q"','name="q" value="'.str_replace(array('\\"','"'),array('&#34;','&#34;'),Formatter::unEsc($query)).'"',$search_part);
		}
	}
	$ts=(strpos($f->template_source,'../')!==false)?$f->template_source:'../'.$f->template_source;

	if(strpos($template_content,'%SEARCH_OBJECT%')!==false) $pattern='%SEARCH_OBJECT%';
	elseif(file_exists($ts) && strpos($template_content,'%CONTENT%')!==false) $pattern='%CONTENT%';
	else $pattern=PageHandler::getArea($template_content);

	if($search_part!='') $result_output=$search_part.$result_output;
	$template_content=str_replace($pattern,$result_output,$template_content);
	if($indir)
		{$template_content=str_replace('</title>','</title>'.F_LF.'<base href="'.str_replace('documents','',$s_full_path_to_script).'">',$template_content);}
	return $template_content;
}
function replace_accents($src)
{
	global $AccChars,$NormChars;
	$res=str_replace($AccChars,$NormChars,$src);
	return $res;
}
function preg_pos($sPattern,$sSubject,&$occurances,&$score)
{
	global $f;

	$sSubject=str_replace(array('&#8221;','&#8220;','&#8216;','&#8217;'),array('�', '�', '�', '�'), $sSubject);
	$sPattern=Formatter::strToLower(replace_accents($sPattern));
	$sSubject=Formatter::strToLower(replace_accents($sSubject));
	$wildcardPos=false;
	if(strpos($sPattern,'*')!==false) {$wildcardPos=strpos($sPattern,'*'); $wc='*'; }
	elseif(strpos($sPattern,'?')!==false) {$wildcardPos=strpos($sPattern,'?'); $wc='?'; }

	if($wildcardPos!==false && $wildcardPos==strlen($sPattern)-1) $sPattern_='/\W('.str_replace($wc,'',$sPattern).')/i';
	elseif($wildcardPos!==false && $wildcardPos==0) $sPattern_='/('.str_replace($wc,'',$sPattern).')\W/i';
	elseif($wildcardPos!==false) $sPattern_='/('.str_replace($wc,'.\w*?',$sPattern).')\W/i';
	else $sPattern_='/'.($f->uni?'':'\b').'('.$sPattern.')'.($f->uni?'':'\b').'/i';
	$occurances=@preg_match_all($sPattern_,$sSubject,$aMatches,PREG_OFFSET_CAPTURE);
	if($occurances>0)
	{
		$keywords=explode('|',$sPattern);
		foreach($aMatches[0] as $k=>$v) $temp_arr[]=$v[0];
		$string_with_matches=implode(' ',$temp_arr);
		foreach($keywords as $k=>$word) {if(!empty($word) && strpos($string_with_matches,$word)!==false) $score++;}
		return $aMatches;
	}
	else return false;
}
function cut_result($haystack,$needle_pos,$key_words_s)
{
	global $f;

	$haystack=str_replace(array('&#8221;', '&#8220;', '&#8216;', '&#8217;'), array('�', '�', '�', '�'), $haystack);
	if(strlen($haystack)>400)
	{
		$x=0; $y=400;
		while(($needle_pos-$x>0) && (substr($haystack,$needle_pos-$x-1, 1)!='.') && (substr($haystack,$needle_pos-$x-1, 1)!='!') && (substr($haystack,$needle_pos-$x-1, 1)!='?') )		{$x += 1; }
		while((substr($haystack,$needle_pos+$y, 1)!=' ') && ($needle_pos+$y>$needle_pos) )  {$y-=1; }
		$res_block=substr($haystack,$needle_pos-$x, $x+$y);
	}
	else $res_block=$haystack;

	$wildcardPos=false;
	if(strpos($key_words_s,'*')!==false)	{$wildcardPos=strpos($key_words_s,'*'); $wc='*'; $key_words_s=str_replace($wc,'.\w*?',$key_words_s);}
	elseif(strpos($key_words_s,'?')!==false) {$wildcardPos=strpos($key_words_s,'?'); $wc='?'; $key_words_s=str_replace($wc,'.\w*?',$key_words_s);}

	$substr=false;
	$key_words_s_a=explode("|",$key_words_s);
	$orig_res_block=$res_block;
	$res_block=Formatter::strToLower(($res_block));

	foreach($key_words_s_a as $k=>$v)
	{
		$v=Formatter::strToLower(($v));
		$res_block=($substr)?preg_replace("/(".$v.")/i", "[;:]$1[:;]",$res_block):preg_replace("/(\W|\A|".($f->uni?'':'\b').")(".$v.")(\W|\Z|".($f->uni?'':'\b').")/i", "$1[;:]$2[:;]$3",$res_block);
	}

	if($res_block!=$orig_res_block)
	{
		for($i=0;$i<strlen($res_block);$i++)
		{
			if($res_block[$i]=='[')
			{
				if(substr($res_block,$i,4)=='[;:]') $orig_res_block=substr($orig_res_block,0,$i).'[;:]'.substr($orig_res_block,$i,100000);
				elseif (substr($res_block,$i,4)=='[:;]') $orig_res_block=substr($orig_res_block,0,$i).'[:;]'.substr($orig_res_block,$i,100000);
			}
		}
	}

	$res_block=str_replace("[;:]",'<span class="search_highlight"><b>',$orig_res_block);
	$res_block=str_replace("[:;]",'</b></span>',$res_block);
	$res_block=$res_block.(strlen($haystack)>100?" <b>...</b> ":" ");
	return $res_block;
}
function extract_records($fname,$id,$entry_id='',$db_fields='')
{
	global $f;

	$records=array();
	if(file_exists($fname))
	{
		if($id=='144')
		{
			$records_str=File::read($fname);
			if($records_str!='')
			{
				$records_str=Formatter::GFS($records_str,'<entries>','</entries>');
				$records=format_in_array2($records_str);
			}
			if($entry_id!='' && !empty($records)) // when extracting single record
			{
				foreach($records as $k=>$v) {if(in_array($entry_id,$v)) {$temp[]=$v; break;} }
				$records=$temp;
			}
		}
		else
		{
			if(filesize($fname)>0)
			{
				$fp=fopen($fname,'r');
				$php_start=fgetcsv($fp,2048);
				$db_field_names=fgetcsv($fp,2048); if($db_fields!='') $db_field_names=$db_fields;
				while($data=fgetcsv($fp,$f->max_chars))
				{
					if($data[0]!="*/ ?>")
					{
						if($entry_id!='') // when extracting single record
						{
							if($data[0]==$entry_id) {$records[]=format_in_array1($data,$db_field_names); break; }
							else continue;
						}
						else $records[]=format_in_array1($data,$db_field_names);
					}
				}
				fclose($fp);
			}
		}
	}
	return $records;
}
function format_in_array1($values,$keys)
{
	$output=array(); $index=0;
	foreach($keys as $k=>$v)
	{
		if($v=='Creation_Date') {$output[$v]=(!isset($values[$index]) || $values[$index]=='')? $values[array_search('Id',$keys)]: $values[$index];}
		else
		{
			if(isset($values[$index]) && $v=='Publish_Status' && $values[$index]=='') $output[$v]='1';
			elseif(isset($values[$index]) && $v=='Accessibility' && $values[$index]=='') $output[$v]='1';
			elseif(isset($values[$index])) $output[$v]=$values[$index];
			elseif(!isset($output[$v])) $output[$v]=($v=='Publish_Status' || $v=='Accessibility')?'1':'';
		}
		$index++;
	}
	return $output;
}
function format_in_array2($records)
{
	$entries_array=array();
	$i=1;

	while(strpos($records, '<entry id="'.$i.'">')!==false)
	{
		$comments_buff=array();
		$main_buffer['id']=$i;

		$record='<entry id="'.$i.'">'. Formatter::GFS($records, '<entry id="'.$i.'">', '</entry>').'</entry>';
		$entry_part=Formatter::GFS($record, '<entry id="'.$i.'">', '<comments_data>');
		$comments_part=Formatter::GFS($record, '<comments_data>', '</comments_data>');
		$entry_timetsamp=Formatter::GFS($entry_part, "<timestamp>", "</timestamp>");

		while(strpos($entry_part, '<')!==false)
		{
			$element_name=Formatter::GFS($entry_part, '<', '>');
			$element_value=Formatter::GFS($entry_part, "<$element_name>", "</$element_name>");
			$main_buffer [$element_name]=$element_value;
			$entry_part=str_replace("<$element_name>$element_value</$element_name>", '',$entry_part);
		}
		$j=1;
		while(strpos($comments_part, '<comment id="'.$j.'">')!==false)
		{
			$buff=array();
			$comment_str=Formatter::GFS($comments_part, '<comment id="'.$j.'">', '</comment>');
			while(strpos($comment_str, '<')!==false)
			{
				$element_name=Formatter::GFS($comment_str, '<', '>');
				$element_value=Formatter::GFS($comment_str, "<$element_name>", "</$element_name>");
				$buff [$element_name]=$element_value;
				$comment_str=str_replace("<$element_name>$element_value</$element_name>", '',$comment_str);
			}
			$buff['entry_id']=$entry_timetsamp;
			$comments_buff []=$buff;
			$j++;
		}
		$main_buffer ['comments']=$comments_buff;
		$entries_array []=$main_buffer;
		$i++;
	}
	return $entries_array;
}
function db_search($query,$key_words_s,$pages_list,$language,$cat_name='')
{
	global $f,$s_db_dir;

	if(!$f->use_linefeed && (in_array('Windows-1251',$f->site_charsets_a) || $f->uni)) $linux_cyrillic_fl=true; else $linux_cyrillic_fl=false;
	$multi_key=substr_count($key_words_s,'|')>0;

/*	if($f->uni)
	{
		foreach($AccChars as $k=>$v) $AccChars[$k]=utf8_encode($v);
		foreach($NormChars as $k=>$v) $NormChars[$k]=utf8_encode($v);
	} */
	$result_pages=array();
	$search_db_fname=array();
	$search_in_cur_lang='true';
	$fl=true;

	foreach($f->site_languages_a as $k=>$v)	// check for auto reindex
	{
		$ff=$s_db_dir.'search_db_'.($k+1).'.ezg.php';
		if(file_exists($ff)) {$fsize=filesize($ff); if($fsize>0) {$fl=false; break;} }
	}
	if($fl) reindex(true);

	if(isset($_REQUEST['sa'])) $search_in_cur_lang=Formatter::stripTags($_REQUEST['sa']);

	if($search_in_cur_lang=='false' || $search_in_cur_lang=='FALSE')
	{
		foreach($f->site_languages_a as $k=>$v) $search_db_fname[]=$s_db_dir.'search_db_'.($k+1).'.ezg.php';
	}
	else $search_db_fname[]=$s_db_dir.'search_db_'.$language.'.ezg.php';

	foreach($search_db_fname as $k=>$file)
	{
		$content=File::read($file);
		if($content!='')
		{
			foreach($pages_list as $k=>$v)
			{
				$db_content='';
				$s_page_id=str_replace('<id>','',$v[10]);

				if(strpos($content, '<page_id_'.$s_page_id.'>')!==false)
				{
					$page_info=Formatter::GFS($content,'<page_id_'.$s_page_id.'>','</page_id_'.$s_page_id.'>');
					$page_title=Formatter::GFS($page_info,'<page_title>','</page_title>');
					$page_url=Formatter::GFS($page_info,'<page_url>','</page_url>');
					$lm_date=Formatter::GFS($page_info,'<page_date>','</page_date>');

					//handling db content
					if(in_array($v[4],array('20','136','138','144','143','137','99')))
					{
						$db_content=Formatter::GFS($page_info,'<db_content>','</db_content>');
						if($db_content!='')
						{
							$haystack=Formatter::unEsc(urldecode($db_content));
							if($linux_cyrillic_fl) @preg_match_all("/(<id_.*._id>)(.*)(<\/id_.*._id>)/U",$haystack,$entries);
							else @preg_match_all("/(<id_.*._id>)(.*)(<\/id_.*._id>)/U",$haystack,$entries); //\w
							if(is_array($entries))
							{
								foreach($entries[0] as $val)
								{
									$occurances=0;$score=0;
									$matches=preg_pos($key_words_s,$val,$occurances,$score);
									if(is_array($matches))
									{
										$page_url_fixed=$page_url;
										$entry_id=Formatter::GFS($val,'<id_','_id>');
										$entry_data=Formatter::GFS($val,'<id_'.$entry_id.'_id>','</id_'.$entry_id.'_id>');
										$entry_cat=Formatter::GFS($entry_data,'%%c_','_c%%');

										if($cat_name=='' || (is_array($cat_name) && in_array(trim($entry_cat),$cat_name)) || strpos($entry_cat,$cat_name)!==false)
										{
											if($v[4]=='136') $page_url_fixed.='?event_id='.$entry_id;
											elseif($v[4]=='138') $page_url_fixed.='?photo_id='.$entry_id;
											elseif(in_array($v[4], array('144','143','137'))) $page_url_fixed.='?entry_id='.$entry_id;
											elseif($v[4]=='99')
											{
												$ext=(strpos($page_url_fixed,'.htm')!==false)? '.htm': '.php';
												$page_url_fixed=str_replace($ext, '_'.$entry_id.$ext, $page_url_fixed);
											}

											if($multi_key && strpos($entry_data,$query)!==false)$score+=100;
											$fixed_pos=$matches[0][0][1]-strlen('<id_'.$entry_id.'_id>');
											$lm_date=Formatter::GFS($entry_data,'%%lm_','_date%%');
											$entry_title=Formatter::GFS($entry_data,'%%title_','_title%%');
											$entry_data=str_replace(array('%%lm_'.$lm_date.'_date%%','%%title_'.$entry_title.'_title%%','%%c_','_c%%'),'',$entry_data);
											$entry_data=cut_result($entry_data,$fixed_pos,$key_words_s);
											$result_pages["$page_url_fixed"]=array($page_title,$page_url_fixed,$entry_data,$s_page_id,$lm_date, $occurances,$score,$entry_title,$entry_cat);
										}
									}
								}
							}
						}
					}
					//handling main page
					$occurances=0;$score=0;
					$page_content=Formatter::GFS($page_info,'<page_content>','</page_content>');
					$page_content=Formatter::unEsc($page_content);
					$matches=preg_pos($key_words_s,$page_content,$occurances,$score);
					if(is_array($matches))
					{
						if($multi_key && strpos($page_content,$query)!==false)$score+=100;
						$result_pages ["$page_url"]=array($page_title, $page_url,cut_result($page_content,$matches[0][0][1],$key_words_s),$s_page_id,$lm_date,$occurances,$score);
					}
				}
			}
			if(strpos($content,'<ext_pages>')!==false)
			{
				$ext_content=Formatter::GFS($content,'<ext_pages>','</ext_pages>');
				while(strpos($ext_content,'<page_id_')!==false)
				{
					$occurances=0;$score=0;
					$page_info=Formatter::GFS($ext_content,'<page_id','</page_id');
					$page_title=Formatter::GFS($page_info,'<page_title>','</page_title>');
					$page_url=Formatter::GFS($page_info,'<page_url>','</page_url>');
					$lm_date=Formatter::GFS($page_info,'<page_date>','</page_date>');
					$page_content=Formatter::GFS($page_info,'<page_content>','</page_content>');
					$page_content=Formatter::unEsc($page_content);

					$matches=preg_pos($key_words_s,$page_content,$occurances,$score);
					if(is_array($matches))
					{
						if(!array_key_exists("$page_url",$result_pages))
						{
							$needle_pos=$matches[0][0][1];
							if($multi_key && strpos($page_content,$query)!==false)$score+=100;
							$result_pages ["$page_url"]=array($page_title, $page_url,cut_result($page_content,$needle_pos,$key_words_s),$page_url,$lm_date,$occurances,$score);
						}
					}
					$ext_content=substr($ext_content,strpos($ext_content,'</page_id')+9);
				}
			}
		}
	}
	return $result_pages;
}
function reindex($auto=false)
{
	global $f,$s_php_pages_ids,$s_rel_path,$query_st_time,$s_db_dir,$s_index_template_areas;

	$output='';
	foreach($f->site_languages_a as $kkk=>$vvv)
	{
		$buffer='';
		clearstatcache();
		$search_db_fname=$s_db_dir.'search_db_'.($kkk+1).'.ezg.php';

		if(file_exists($search_db_fname))
		{
			$page_reindex=(isset($_GET['pid']) && filesize($search_db_fname)>0? true: false);
			if($page_reindex)	$pages_list[]=CA::getPageParams(Formatter::stripTags($_GET['pid']),$s_rel_path);
			else $pages_list=CA::getSitemap($s_rel_path);

			if(!$page_reindex) $buffer.="<?php echo 'hi'; exit; /* ";
			foreach($pages_list as $k=>$v)
			{
				$p_lang=array_search ($v[16],$f->site_languages_a);
				$page_title=(strpos($v[0],'#')!==false && strpos($v[0],'#')==0? str_replace('#','',$v[0]): $v[0]);
				$id=str_replace('<id>', '', $v[10]);

				if(strpos($v[1],'http:')===false && strpos($v[1],'https:')===false && $p_lang==$kkk && $v[20]=='FALSE') // ignore 'HIDDEN in search'
				{
					if($v[4]=='148' || $v[4]=='150') $content='';
					elseif(!in_array($v[4],$s_php_pages_ids))		// for NORMAL pages and PHP REQUEST pages
					{
						$main_fname=(strpos($v[1],'../')===false?'../'.$v[1]:$v[1]);
						$content=File::read($main_fname);
						$lm_date=Formatter::GFS($content,'<meta name="date" content="','"');
						$content=PageHandler::getArea($content,true);

						if($v[4]=='99')
						{
							if(strpos($v[1],'/')===false)  {$i_content=File::read('i_'.$main_fname);}
							else {$ff=substr($v[1], strrpos($v[1],'/')+1); $i_content=File::read(str_replace($ff, 'i_'.$ff, $v[1]));}
							if(!empty($i_content)) $i_content=Formatter::clearHtml(PageHandler::getArea($i_content,true));
							$content.=' '.$i_content;

							$db_part='';
							$ext=(strpos($v[1],'.htm')!==false)? '.htm': '.php';
							$count=2;
							$more_file=str_replace($ext, '_'.$count.$ext, $main_fname);
							while(file_exists($more_file))
							{
								$db_part.='<id_'.$count.'_id>'.Formatter::clearHtml(PageHandler::getContent($more_file,$s_index_template_areas)).'</id_'.$count.'_id>';
								$count++;
								$more_file=str_replace($ext, '_'.$count.$ext, $main_fname);
							}
						}
						if(strpos($content, '<?php get_editable_tag(')!==false)
						{
							$oep_content=File::read($s_db_dir.$id.'.ezg.php'); $area_id=1;
							while(strpos($oep_content,'<ea_'.$area_id.'>')!==false)
							{
								$content.=' '.Formatter::GFS($oep_content,'<ea_'.$area_id.'>','</ea_'.$area_id.'>');
								$area_id++;
							}

						}
						$buffer.='<page_id_'.$id.'><page_title>'.$page_title.'</page_title><page_date>'.$lm_date.'</page_date><page_url>'.$v[1] .'</page_url><page_content>'.Formatter::clearHtml($content).'</page_content>'
						.(isset($db_part)? '<db_content>'.$db_part.'</db_content>':'').'</page_id_'.$id.'>';
					}
					else // for special PHP pages
					{
						$db_part='';
						if($v[4]=='20') // OEP
						{
							$dir=(strpos($v[1],'../')===false)?'../':'../'.Formatter::GFS($v[1],'../','/').'/';
							if($v[4]=='20')	$main_fname=(Validator::checkProtection($v) > 1? $dir.$id.'.php': $dir.$id.'.html');
							else $main_fname=$dir.$id.'.html';
							$content=PageHandler::getContent($main_fname,$s_index_template_areas);
							$content=Formatter::clearHtml($content);

							$main_db_content='';
							$db_fname=$s_db_dir.$id.'.ezg.php';
							if(file_exists($db_fname))
							{
								$lm_date=array(filemtime($db_fname));
								$main_db_content=File::read($db_fname);

								if(strpos($main_db_content,'<ea_main')!==false)
								{
									$main_ea=Formatter::GFS($main_db_content,'<ea_main>','</ea_main>');
									$db_part.=$main_ea;
									$main_db_content=str_replace('<ea_main>'.$main_ea.'</ea_main>','',$main_db_content);
								}
								while(strpos($main_db_content,'<ea_')!==false)
								{
									$area_id=Formatter::GFS($main_db_content,'<ea_','>');
									if(strpos($main_db_content,'</ea_'.$area_id.'>')===false) break;
									$more_ea=Formatter::GFS($main_db_content,'<ea_'.$area_id.'>','</ea_'.$area_id.'>');
									$db_part.=$more_ea;
									$main_db_content=str_replace('<ea_'.$area_id.'>'.$more_ea.'</ea_'.$area_id.'>','',$main_db_content);
								}
								$content.=' '.Formatter::clearHtml($db_part);
								$buffer.='<page_id_'.$id.'><page_title>'.$page_title.'</page_title><page_date>'.max($lm_date).'</page_date> <page_url>'.$v[1].'</page_url><page_content>'.$content.'</page_content></page_id_'.$id.'>';
							}
						}
						elseif(in_array($v[4],array('133')))  // subscribe page
						{
							$dir=(strpos($v[1],'../')===false)?'../':'../'.Formatter::GFS($v[1],'../','/').'/';

							if(empty($v[9])) $fname=(Validator::checkProtection($v) > 1? $dir.$id.'.php': $dir.$id.'.html');
							elseif(strpos($v[9],'.')===false) $fname=(Validator::checkProtection($v) > 1? $dir.$v[9].'.php': $dir.$v[9].'.html');
							else $fname=$dir.$v[9];
							$content=File::read($fname);
							$lm_date=Formatter::GFS($content,'<meta name="date" content="','"');
							$content=PageHandler::getArea($content,true);
							$content=Formatter::clearHtml($content);
							$buffer.='<page_id_'.$id.'><page_title>'.$page_title.'</page_title><page_date>'.$lm_date.'</page_date><page_url>'.$v[1]. '</page_url><page_content>'.$content.'</page_content></page_id_'.$id.'>';
						}
						elseif(in_array($v[4],array('136','137','138','143','144')))  //blog, pblog, cal, podcast, guestbook
						{
							$dir=(strpos($v[1],'../')===false)?'../':'../'.Formatter::GFS($v[1],'../','/').'/';
							$main_fname=(Validator::checkProtection($v) > 1? $dir.$id.'.php': $dir.$id.'.html');
							$content=PageHandler::getContent($main_fname,$s_index_template_areas);

							if($v[4]=='138')
							{
								$fname_arch=$dir.($id+1).'.html';
								$content.=PageHandler::getContent($fname_arch,$s_index_template_areas);
							}
							$content=Formatter::clearHtml($content);
							$content=Formatter::clearMacros($content, $v[4]);
							$dir='../'.Formatter::GFS($v[1],'../','/').'/';

							if(in_array($v[4], array('137', '138', '143')))		// blog, photoblog, podcast
							{
								if($v[4]=='137') $db_fields=array("Id","Category","Title","Content","Image_Url","Last_Modified","Allow_Comments", "Allow_Pings","Entry_Excerpt","Keywords","Publish_Status","User","Creation_Date","Accessibility");
								elseif($v[4]=='138') $db_fields=array("Id","Category","Title","Content","Image_Url","Thumbnail_Url", "Last_Modified", "Keywords","Publish_Status","User","Creation_Date","Allow_Comments");
								else $db_fields=array("Id","Category","Title","Subtitle","Author","Content","Explicit","Keywords", "Duration", "Block","Mediafile_Url","Mediafile_Size","Image_Url","Last_Modified","Publish_Status","User","Creation_Date","Accessibility");
								$com_db_fields=array(($v[4]=='138'?"Photo_Id":"Entry_Id"),"Timestamp","Visitor","EmailAddress","Url","Comments","IP","HOST","AGENT","Approved");

								$db_fname=$s_db_dir.$id.'_db_blog_entries.ezg.php';
								if($page_reindex && isset($_GET['entryid']))
									$entries_records=extract_records($db_fname, $v[4], Formatter::stripTags($_GET['entryid']), $db_fields);
								else $entries_records=extract_records($db_fname, $v[4], '', $db_fields);

								$categories_info=File::read($s_db_dir.$id.'_blocked_ips.ezg.php');
								if(!empty($entries_records))
								{
									foreach ($entries_records as $key=>$val)
									{
										if($val['Publish_Status']=='1' && ($v[4]=='138' || $val['Accessibility']=='1'))
										{
											$tit=Formatter::clearHtml(urldecode($val['Title']));
											$db_part.='<id_'.$val['Id'].'_id>'.$tit;
											if($v[4]=='143')
											{
												if(!empty($val['Subtitle'])) $db_part.=' '.Formatter::clearHtml(urldecode($val['Subtitle']));
												if(!empty($val['Author']))	 $db_part.=' '.Formatter::clearHtml(urldecode($val['Author']));
											}
											if(!empty($val['Content'])) $db_part.=' '.Formatter::clearHtml(urldecode($val['Content']));
											if(!empty($val['Keywords'])) $db_part.=' '.Formatter::clearHtml(urldecode($val['Keywords']));
											$db_part.=' %%c_ '.Formatter::GFS($categories_info,'<cat_'.$val['Category'].'>','%%').' _c%%';
											$db_part.=' %%lm_'.$val['Last_Modified'].'_date%%'.'%%title_'.$tit.'_title%%';
											$db_part.='</id_'.$val['Id'].'_id>';
										}
									}
								}
								if(!empty($db_part))
								{
									$comments_records=extract_records($s_db_dir.$id.'_db_blog_comments.ezg.php', $v[4]);
									if (!empty($comments_records))
									{
										foreach ($comments_records as $key=>$val)
										{
											if(!isset($val['Approved']) || $val['Approved']=='1')
											{
												$m='</id_'.($v[4]=='138'?$val['Photo_Id']:$val['Entry_Id']).'_id>';
												$db_part=str_replace($m,' '.Formatter::clearHtml(urldecode($val['Visitor'])).$m, $db_part);
												if(!empty($val['Comments']))
												{
													$db_part=str_replace($m,' '.Formatter::clearHtml(urldecode($val['Comments'])).$m, $db_part);
												}
											}
										}
									}
								}
							}
							elseif(in_array($v[4], array('136')))   // calendar
							{
								$db_fname=$s_db_dir.$id.'_cal_events.ezg.php';
								if($page_reindex && isset($_GET['entryid']))
									$entries_records=extract_records($db_fname,$v[4],Formatter::stripTags($_GET['entryid']));
								else $entries_records=extract_records($db_fname,$v[4]);
								$categories_info=File::read($s_db_dir.$id.'_blocked_ips.ezg.php');

								if(!empty($entries_records))
								{
									foreach($entries_records as $key=>$val)
									{
										$tit=Formatter::clearHtml(urldecode($val['Short_description']));
										$db_part.='<id_'.$val['Id'].'_id>'.$tit;
										if(!empty($val['Details'])) $db_part.=' '.Formatter::clearHtml(urldecode($val['Details']));
										if(!empty($val['Location'])) $db_part.=' '.Formatter::clearHtml(urldecode($val['Location']));
										$db_part.=' '.Formatter::GFS($categories_info,'<cat_'.$val['Category'].'>','%%');
										$db_part.='%%lm_'.filemtime($db_fname).'_date%%'.'%%title_'.$tit.'_title%%';
										$db_part.='</id_'.$val['Id'].'_id>';
									}
								}
							}
							elseif(in_array($v[4], array('144')))   // guestbook
							{
								$db_fname=$s_db_dir.$id.'_db_guestbook.ezg.php';
								if($page_reindex && isset($_GET['entryid']))
									$entries_records=extract_records($db_fname,$v[4],Formatter::stripTags($_GET['entryid']));
								else $entries_records=extract_records($db_fname,$v[4]);

								if(!empty($entries_records))
								{
									foreach ($entries_records as $key=>$val)
									{
										if(!isset($val['approved']) || $val['approved']=='1')
										{
											$nn=Formatter::clearHtml(urldecode($val['name']));
											$sn=Formatter::clearHtml(urldecode($val['surname']));
											$db_part.='<id_'.$val['timestamp'].'_id>'.$nn;
											if(!empty($val['surname'])) $db_part.=' '.$sn;
											$db_part.=' '.Formatter::clearHtml(urldecode($val['content']));
											if(!empty($val['country'])) $db_part.=' '.Formatter::clearHtml(urldecode($val['country']));
											foreach($val['comments'] as $ka=>$va)
											{
												if(!empty($va) && (!isset($va['approved']) || $va['approved']=='1'))
												{
													$db_part.=' '.Formatter::clearHtml(urldecode($va['visitor']));
													$db_part.=' '.Formatter::clearHtml(urldecode($va['comments']));
												}
											}
											$db_part.='%%lm_'.$val['timestamp'].'_date%%'.'%%title_'.$nn.' '.$sn.'_title%%';
											$db_part.='</id_'.$val['timestamp'] .'_id>';
										}
									}
								}
							}
							if($page_reindex && isset($_GET['entryid'])) $buffer.=$db_part;
							else $buffer.='<page_id_'.$id.'><page_title>'.$page_title.'</page_title><page_url>'.$v[1].'</page_url><page_content>' .$content.'</page_content>'.'<db_content>'.$db_part.'</db_content>'.'</page_id_'.$id.'>';
							$db_part='';
						}
					}
					$content='';
				}
			}
			if(!$page_reindex)
			{
				$buffer.='<ext_pages>';
				$buffer.='</ext_pages>';
				$buffer.=" */ ?>";
			}

			if(!empty($buffer) || $page_reindex)
			{
				if(!$page_reindex)
				{if(!$fp=@fopen($search_db_fname,"w")) {print Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD',$search_db_fname);exit;}}
				else
				{if(!$fp=@fopen($search_db_fname,"r+")) {print Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD',$search_db_fname);exit;}}

				flock($fp, LOCK_EX);
				if($page_reindex)
				{
					$db_existing_content=fread($fp,filesize($search_db_fname));  $pg=Formatter::stripTags($_GET['pid']);
					if(isset($_GET['entryid']) && strpos($db_existing_content,'<page_id_'.$pg.'>')!==false)
					{
						$en_id=Formatter::stripTags($_GET['entryid']);
						$page_for_repl=Formatter::GFSAbi($db_existing_content,'<page_id_'.$pg.'>','</page_id_'.$pg.'>');
						if(strpos($page_for_repl,'<id_'.$en_id.'_id>')!==false)
						{
							$for_repl=Formatter::GFSAbi($page_for_repl,'<id_'.$en_id.'_id>','</id_'.$en_id.'_id>');
							$db_existing_content=str_replace($for_repl,$buffer,$db_existing_content);
						}
						else
						{
							$buffer=str_replace('</db_content>',$buffer.'</db_content>',$page_for_repl);
							$db_existing_content=str_replace($page_for_repl,$buffer,$db_existing_content);
						}
					}
					elseif(strpos($db_existing_content,'<page_id_'.$pg.'>')!==false)
					{
						$page_for_repl=Formatter::GFSAbi($db_existing_content,'<page_id_'.$pg.'>','</page_id_'.$pg.'>');
						$db_existing_content=str_replace($page_for_repl,$buffer,$db_existing_content);
					}
					else break;
					$buffer=$db_existing_content;
					ftruncate($fp,0);
					fseek($fp,0);
				}
				if(fwrite($fp, $buffer)===FALSE) {print "Cannot write to file ($search_db_fname)"; exit;}
				flock($fp,LOCK_UN);
				fclose($fp);
				$output='<div style="position:relative"><span class="rvts8" style="font-variant:small-caps"><b>Site Search successfully reindexed!</b></span>
				<a style="position:absolute;right:130px;" href="../documents/centraladmin.php?process=index" target="_top">Go to Online Administration</a>
				<a style="position:absolute;right:10px;" href="../'.($f->intro_page!=''?$f->intro_page:$f->home_page).'" target="_top">Remove Frame</a></div>';
			}
		}
		elseif($auto==false && !isset($_GET['redirect'])) $output=Formatter::fmtErrorMsg('MISSING_DBFILE',$search_db_fname);
	}
	if($auto==false && !isset($_GET['redirect'])) print $output;

	if(isset($_GET['redirect']))  // auto reindex after online update
	{
		$redirect_url=Formatter::stripTags($_GET['redirect']);
		Linker::redirect('../'.$redirect_url,false,' />');
	}
}
function process_search($call_from_outside=false,$search_in_page='',$cat_name='')
{
	global $f,$s_rel_path,$version,$s_gt_page,$query_st_time,$s_lang_settings, $script_path,$showresulttime,
					$s_output_params,$s_date_params;

	$action_id=isset($_REQUEST['action'])?Formatter::stripTags($_REQUEST['action']):'search';
	if($action_id=="index")
	{
		
		$indexUrl=($f->intro_page!=''?$f->intro_page:$f->home_page);
		if(stripos($indexUrl,'http')===false)
			$indexUrl='../'.$indexUrl;		
		echo '<frameset rows="40,*" frameborder="0" framespacing="0">
							<frame src="search.php?action=reindex" frameborder="0" scrolling="no">
							<frame src="'.$indexUrl.'" frameborder="0">
						</frameset>';
		exit;
	}
	$query_st_time=Date::microtimeFloat();

	if($action_id=="reindex") reindex();
	elseif($action_id=="version") echo $version;
	elseif($action_id=="search" || $call_from_outside)
	{
		$body_section='';$query='';$id='';$page_info='';$language=1;
		if($search_in_page!='')	$pages_list[]=CA::getPageParams(intval($search_in_page),$s_rel_path);
		else $pages_list=CA::getSitemap($s_rel_path);

		$s_gt_path=((strpos($s_gt_page,'../')===false) && $s_rel_path!=''?'../':'').$s_gt_page; //search output params
		if($s_rel_path=='') $s_gt_path=str_replace('../','',$s_gt_path);
		$s_template_content=File::read($s_gt_path);
		if(strpos($s_template_content,'%SEARCH_OBJECT(')!==false)
		{
			$s_template_content=Formatter::objClearing("SEARCH_OBJECT",$s_template_content);
			$s_output_params_t=Formatter::GFS($s_template_content,'%SEARCH_OBJECT(',')%');
			$s_output_params=Formatter::pTagClearing($s_output_params_t);
			$s_template_content=str_replace("%SEARCH_OBJECT(".$s_output_params_t,"%SEARCH_OBJECT(".$s_output_params,$s_template_content);
			$s_template_content=Formatter::objDivReplacing('%SEARCH_OBJECT('.$s_output_params.')%',$s_template_content);
			$s_template_content=str_replace('%SEARCH_OBJECT('.$s_output_params.')%','%SEARCH_OBJECT%',$s_template_content);

			$s_date_params=(strpos($s_output_params,'%date[')!==false)?Formatter::GFS($s_output_params,'%date[',']%'):Formatter::GFS($s_output_params,'%DATE[',']%');
			$s_output_params=str_replace('%date['.$s_date_params.']%','%date%',$s_output_params);
		}
		$use_params=($s_output_params!='');
		if($use_params && $call_from_outside)
			$s_template_styles=Formatter::GFSAbi($s_template_content,'<style type="text/css">','</style>'); //search output params

		if(isset($_REQUEST['id']) || $search_in_page!='')
		{
			$id=Formatter::stripTags(isset($_REQUEST['id'])? $_REQUEST['id']: $search_in_page);
			foreach($pages_list as $k=>$v) {if(strpos($v[10],'<id>'.$id)!==false) {$page_info=$v;break;} }
			if($page_info!='') $language=array_search($page_info[16],$f->site_languages_a)+1;
		}

		$l_results=(isset($s_lang_settings['Result']))? $s_lang_settings['Result']: 'Result';
		$l_page=(isset($s_lang_settings['Page']))? $s_lang_settings['Page']: 'page';
		$l_from=(isset($s_lang_settings['From']))? $s_lang_settings['From']: 'from';
		$l_search=(isset($s_lang_settings['Search']))? $s_lang_settings['Search']: 'Search';

		$show_results=(isset($_REQUEST['mr']) && !empty($_REQUEST['mr']))?Formatter::stripTags($_REQUEST['mr']):10;
		$page=(isset($_REQUEST['page']))?Formatter::stripTags($_REQUEST['page']):1;
		$search_in_cur_lang=(isset($_REQUEST['sa']))?Formatter::stripTags($_REQUEST['sa']):'true';
		settype($page,"integer"); settype($show_results,"integer");

		if(isset($_REQUEST['q']) || isset($_REQUEST['query']) || isset($_REQUEST['string']))
		{
			$query=(isset($_REQUEST['q'])?$_REQUEST['q']:(isset($_REQUEST['query'])?$_REQUEST['query']:$_REQUEST['string']));
			$query=Formatter::unEsc(Formatter::stripTags(trim($query)));
			if($query=='|' || $query=='"' || $query=='\'' || $query=='\\') $query='';

			if($query!='')
			{
				$q_pos=strpos($query,'"'); // opening " (if used)
				$qs_pos=strpos($query,'\"');
				$qcl_pos=strrpos($query,'"'); // closing " (if used)
				if( (($q_pos!==false && $q_pos==0) || ($qs_pos!==false && $qs_pos==0)) && $qcl_pos==(strlen($query)-1))
				{
					$query=substr($query,1,strlen($query)-2);
					if(strpos($query,'\\')!==false) $query=substr($query,1,strlen($query)-2);
					$key_words=array($query);
				}
				else {$key_words=(strpos($query,' ')!==false? explode(' ',$query): array($query));}

				$key_words_trimmed=array();
				foreach($key_words as $k=>$v)  {if($v!='') {$key_words_trimmed[]=trim($v);} }
				$key_words_s=implode('|',$key_words_trimmed);

				$results=db_search($query,$key_words_s,$pages_list,$language,$cat_name);
				$count_res=count($results);
				$first_id=(($page-1)*$show_results+1);
				$last_id=($show_results*$page>$count_res?$count_res:$show_results*$page);

				$main_result=''; $main_result_head='';

				$res_header=$l_results.' : ' .Formatter::sth($query);
				if(strpos($s_template_content,'%HEADER%')!==false  && !$call_from_outside)
					$s_template_content=str_replace('%HEADER%',$res_header,$s_template_content);
				else $main_result_head='<div class="search_heading"><span class="rvts24">'.$res_header.F_BR.F_BR.'</span></div>';

				if(!empty($results))
				{
					$all_pages_list=CA::getSitemap($s_rel_path,false,true);

					if($show_results!=0)
					{
						$nav_url=($call_from_outside? $script_path.'?': (strpos($s_gt_page,'../')===false?'documents/':'../documents/') .'search.php?action=search&amp;').'q='.urlencode($query).(isset($id)?'&amp;id='.$id:'').'&amp;mr='.$show_results .'&amp;sa='.$search_in_cur_lang;

						$def_nav=strpos($s_template_content,'%NAVIGATION')!==false && !$call_from_outside;
						$params='';
						if($def_nav)
						{
							$nav=Formatter::GFSAbi($s_template_content,'%NAVIGATION','%');
							$params=Formatter::GFS($nav,'%NAVIGATION(',')%');
						}

						$res_nav=Navigation::page($count_res,$nav_url,$show_results,$page,$s_lang_settings['From'],'nav',$s_lang_settings,'&amp;','',false,false,'',false,$params);

						if($def_nav) $s_template_content=str_replace($nav,$res_nav,$s_template_content);
						else $main_result.='<div class="search_nav">'.$res_nav.'</div>';
					}
					foreach($results as $key=>$row) {$by_score[$key]=$row[6]; $by_occur[$key]=$row[5]; }
					if(count($key_words)>1) array_multisort($by_score,SORT_DESC,$by_occur,SORT_DESC,$results);
					else array_multisort($by_occur,SORT_DESC,$results);
					$results_cut=(count($results)>$show_results && $show_results!=0)?array_slice($results,($page-1)*$show_results,$show_results):$results;
					$counter=($page-1)*$show_results;

					$main_result.=(!$use_params?'<div class="search_blocks">':'');
					foreach($results_cut as $k=>$v)
					{
						$counter++;
						$lm_date='';
						if(isset($v[4]) && !empty($v[4]))
						{
							$lm_date.=(!$use_params?$s_lang_settings['last modified'].': ':'');
							if(strpos($v[4],'-')!==false)
							{
								list($year,$month,$day)=explode('-',$v[4]);
								$lm_date_tstamp=mktime(0,0,0,(integer)$month,(integer)$day,(integer)$year);
							}
							else $lm_date_tstamp=intval($v[4]);

							if(!$use_params) $lm_date.=date('j M Y',$lm_date_tstamp).(!$use_params?' - ':'');
							else $lm_date.=Date::format($lm_date_tstamp,$s_date_params,$f->month_names,$f->day_names,"long");
						}
						if($call_from_outside) $url=$f->http_prefix.Linker::getHost().$_SERVER['PHP_SELF'].substr($v[1],strrpos($v[1],'?'));
						else $url=$f->http_prefix .str_replace('documents','',Linker::getHost().dirname($_SERVER['PHP_SELF'])) .str_replace('../','',$v[1]);

						$title=($call_from_outside? (!empty($v[7])?$v[7]:$v[0]): ($v[0].(empty($v[7])?'':' &gt;&gt; '.$v[7])));
						if(!$use_params)
						{
							$main_result.=($counter%2)?'<div class="search_even">':'<div class="search_odd">';
							$main_result.='<div class="search_title"><span class="rvts0"><b>'.$counter.'.</b></span>&nbsp;<a class="rvts4" href="'.$url.'">'.$title."</a></div>";
							$main_result.='<div class="search_content"><span class="rvts8">'.Formatter::sth2($v[2]).'</span></div>';
							$main_result.='<div class="search_info"><span class="rvts8">'.$lm_date."URL: ".$url."</span></div>";
							$main_result.='</div>';
						}
						else
						{
							if(isset($v[7]) && in_array($v[7],array('136','137','138','143')) && isset($v[8]))  $category=$v[8];
							else $category=str_replace('#','',$all_pages_list[$v[3]][2]);

							$parsed_line=(($counter%2)?'<div class="search_even">':'<div class="search_odd">').$s_output_params.'</div>';
							$parsed_line=str_replace('%counter%',$counter,$parsed_line);
							$parsed_line=str_replace('%title%',$title,$parsed_line);
							$parsed_line=str_replace('%date%',$lm_date,$parsed_line);
							$parsed_line=str_replace('%content%',Formatter::sth2($v[2]),$parsed_line);
							$parsed_line=str_replace('%category%',(isset($category)?$category:''),$parsed_line);
							$parsed_line=str_replace('%url%',$url,$parsed_line);
							$main_result.=$parsed_line;
						}
					}
					$main_result.=(!$use_params?'</div>':'');
				}
				else
				{
					$main_result.='<div class="search_summary"><span class="rvts8">'.$s_lang_settings['no matches found'].'</span></div>'.F_BR;
					$s_template_content=str_replace(array('%NAVIGATION%','%navigation%'),'',$s_template_content);
				}

				$forum_search=false;$forum_result='';$kb_result='';$flstudio_results='';
//*** do not remove this:
//SEARCH_INJECT
//***

				if($forum_search) $body_section.=$forum_result.F_BR.$main_result_head.$main_result.$kb_result.$flstudio_results;
				else $body_section.=F_BR.$main_result_head.$main_result.$kb_result.$forum_result.$flstudio_results;

				$res_generated=$s_lang_settings['page created in'].' '.round(Date::microtimeFloat() - $query_st_time,4);
				if(strpos($s_template_content,'%GENERATEDTIME%')!==false && !$call_from_outside)
					$s_template_content=str_replace('%GENERATEDTIME%',$res_generated,$s_template_content);
				elseif($showresulttime)
					$body_section.=F_BR.'<div class="search_time"><span class="rvts8">'.$res_generated.' '.$s_lang_settings['seconds'].'</div></span>';
			}
			else
			{
				$body_section.=F_BR.'<div class="search_summary"><span class="rvts8">'.$s_lang_settings['search box empty'].'</span></div>'.F_BR;
				$s_template_content=str_replace(array('%NAVIGATION%','%navigation%','%HEADER%','%GENERATEDTIME%'),'',$s_template_content);
			}
			if(!$use_params) $body_section='<div class="search_container">'.$body_section.'</div>';
		}
		if($call_from_outside) {return ($use_params?$s_template_styles:'').$body_section; exit;}

		if(isset($page_info[17])){$pi=$page_info[17];} else $pi='';
		$output=s_GTs($s_template_content,$body_section,$query,$id,$pi);
		print $output;
	}
	/*elseif(isset($_GET['page']) && isset($_GET['highlight']))
	{
		$result_page = '../'.$_GET['page'];
		$f->content = File::read($result_page);
		if(strpos($_GET['page'], '/')===false) {$f->content = str_replace('</title>',
			'</title> <base href="http://'.Linker::getHost().str_replace('documents','',dirname($_SERVER['PHP_SELF'])).'">',$f->content); }
		$p_content = PageHandler::getArea($f->content);

		$key_words_s = urldecode($_GET['highlight']);
		//$key_words_s = preg_quote($key_words_s);
		if(strpos($key_words_s,'*')!==false)
			{$wildcardPos = strpos($key_words_s,'*'); $wc='*'; $key_words_s = str_replace('*', '.w*?', $key_words_s); }
		elseif(strpos($key_words_s,'?')!==false)
			{$wildcardPos = strpos($key_words_s,'?'); $wc='?'; $key_words_s = str_replace('?', '.\w*?', $key_words_s); }
		else {$wildcardPos = false; }

		$pattern = '$1$2<span style="background: #FFFF40;">$3</span>$4$5';
		//$h_content=preg_replace('#(<[^/][^>]*>.*?\b|\W)('.$key_words_s.')(b|\W</[^>]*>)?#msi', $pattern, $p_content);
		$h_content=preg_replace('#(<[^/][^>]*>)(.*?)('.$key_words_s.')(.*?)(</[^>]*>)?#msi', $pattern, $p_content);
		if($wildcardPos!==false)
		{
			$h_content = preg_replace('#(<[^/][^>]*>)(.*?)('.str_replace($wc,'',$key_words_s).')(.*?)(</[^>]*>)?#msi', $pattern, $h_content);
		}
		$f->content = str_replace($p_content,$h_content,$f->content);
		print $f->content;
	}*/
}

process_search();
?>
