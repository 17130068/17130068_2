<?php
$version="functions v4 - 5.2.3 flat";

if(version_compare(PHP_VERSION,'5.0.0','<'))
	die('PHP 5.x required! Please upgrade your PHP version! '.$version);
/*
  http://www.ezgenerator.com
  Copyright (c) 2004-2015 Image-line
  portion of code taken from Mobile_detect.php released under MIT License https://github.com/serbanghita/Mobile-Detect/blob/master/LICENSE.txt
*/

/*
 * Available constants defined somewhere in this file
 * (definition location depends on when the constant value is calculated)
 *
 * F_LF - linefeed
 * F_BR - <br> or <br />
 *
 */

error_reporting(E_ERROR);
define('SEARCH_TABLES_CNT',2);
define('COUNTER_DETAILS_FIELD_CNT',13);

define("NORMAL_PAGE",0);
define("HOME_PAGE",1);

define("BLOG_PAGE",137);
define("BLOG_VIEW",148);

define("PODCAST_PAGE",143);

define("PHOTOBLOG_PAGE",138);
define("PHOTOBLOG_GALLERY_PAGE",139);
define("PHOTOBLOG_VIEW",150);
define("GUESTBOOK_PAGE",144);
define("NEWSLETTER_PAGE",133);
define("CALENDAR_PAGE",136);
define("REQUEST_PAGE",18);
define("OEP_PAGE",20);
define("SURVEY_PAGE",147);

define("SHOP_PAGE",181);
define("SHOP_CATEGORY_PAGE",182);
define("SHOP_PRODUCT_PAGE",183);
define("SHOP_CART_PAGE",184);
define("SHOP_CHECK_PAGE",185);
define("SHOP_RETURN_PAGE",186);
define("SHOP_ERROR_PAGE",187);

define("CATALOG_PAGE",190);
define("CATALOG_CATEGORY_PAGE",191);
define("CATALOG_PRODUCT_PAGE",192);
if(version_compare(PHP_VERSION,'5.4.0','<')&&get_magic_quotes_runtime()==1)
	set_magic_quotes_runtime(0);

class FuncConfig
{

	private $data=array();

	public function __set($name,$value)
	{
		$this->data[$name]=$value;
	}

	public function &__get($name)
	{
		if(array_key_exists($name,$this->data))
		{
			return $this->data[$name];
		}

		$trace=debug_backtrace();
		trigger_error(
			'Undefined property via __get(): '.$name.
			' in '.$trace[0]['file'].
			' on line '.$trace[0]['line'],E_USER_NOTICE);
		return null;
	}

	public function __isset($var)
	{
 		return isset($this->data[$var]);
	}

}

class FuncHolder
{

	protected static $f;

	public function __construct()
	{
		global $f;
		if($f instanceof FuncConfig)
			self::$f=$f;
		else
			die('Settings handler not loaded properly!');
	}

}

//extensions definitions start here
class Unknown extends FuncHolder
{

	public static function xtract($text,$num)
	{
		if(preg_match_all('/\s+/',$text,$junk)<=$num)
			return $text;
		$text=preg_replace_callback('/(<\/?[^>]+\s+[^>]*>)/','Unknown::_abstractProtect',$text);
		$words=0;
		$out=array();
		$stack=array();
		$tok=strtok($text,"\n\t ");
		while($tok!==false and strlen($tok))
		{
			if(preg_match_all('/<(\/?[^\x01>]+)([^>]*)>/',$tok,$matches,PREG_SET_ORDER))
			{
				foreach($matches as $tag)
					self::_recordTag($stack,$tag[1],$tag[2]);
			}
			$out[]=$tok;
			if(!preg_match('/^(<[^>]+>)+$/',$tok))
				++$words;
			if($words==$num)
				break;
			$tok=strtok("\n\t ");
		}
		$result=self::_abstractRestore(implode(' ',$out));
		$stack=array_reverse($stack);
		if($words==$num)
			$result.=' ...';
		foreach($stack as $tag)
			$result.="</$tag>";
		return $result;
	}

	public static function _abstractProtect($match)
	{
		return preg_replace('/\s/',"\x01",$match[0]);
	}

	public static function _abstractRestore($strings)
	{
		return preg_replace('/\x01/',' ',$strings);
	}

	public static function _recordTag(&$stack,$tag,$args)
	{
		if(strlen($args)&&$args[strlen($args)-1]=='/')
			return;
		elseif($tag[0]=='/')
		{
			$tag=substr($tag,1);
			for($i=count($stack)-1; $i>=0; $i--)
			{
				if($stack[$i]==$tag)
				{
					array_splice($stack,$i,1);
					return;
				}
			}
			return;
		}
		elseif(in_array(Formatter::strToLower($tag),array('h1','h2','h3','h4','h5','h6','p','li','ul','ol','div','span','a','strong','b','i','u','em','blockquote','font','h','td','tr','tbody','table')))
			$stack[]=$tag;
	}

	public static function strpos_multi($haystack,$needle_array)
	{
		foreach($needle_array as $k=> $v)
		{
			if(strpos($haystack,$v)!==false)
				return true;
		}
		return false;
	}

	public static function defPostPerDay($mon,$year,$all_posts,$date_field_name)  // define posts for each day in a month
	{
		$posts_per_day[]=array();
		$mktime=Date::tzone(time());
		$t=time();
		$today_ts=mktime(0,0,0,date("n",$t),date("j",$t),date("Y",$t));
		for($i=1; $i<=Date::daysInMonth($mon,$year); $i++)
		{
			$st_i_ts=mktime(0,0,0,$mon,$i,$year);
			$end_i_ts=mktime(23,59,59,$mon,$i,$year);
			foreach($all_posts as $k=> $v)
			{
				if($v[$date_field_name]>=$st_i_ts&&$v[$date_field_name]<=$end_i_ts)
				{
					$posts_per_day[$i]=true;
					break;
				}
			}
		}
		return $posts_per_day;
	}

	public static function isOdd($int)
	{
		return($int&1);
	}

}

class Builder extends FuncHolder
{

	public static function multiboxImages($src,$rel_path,$force=false)
	{
		$multibox=$force||strpos($src,'class="multibox')!==false;
		$mbox=$force||strpos($src,'class="mbox')!==false;

		if($multibox||$mbox)
		{
			$mb=$multibox?'function getMbCl(el){cl=$(el).attr("class").substring(9);return (cl=="")?"LB":cl;};$("a.multibox").each(function(){img=$(this).children("img");if(img.length>0) $(img).attr("class",($(this).attr("class")));else {cl= getMbCl(this);$(this).addClass("mbox").attr("rel","noDesc["+cl+"]");}});$("img.multibox").each(function(){cl= getMbCl(this);fl=$(this).css(\'float\');$(this).parent().addClass(\'mbox\').attr(\'rel\',\'lightbox[\'+cl+\'],noDesc\').css(\'float\',fl);});':'';

			if(strpos($src,'$(".mbox").multibox')!==false)
				$src=str_replace('$(".mbox").multibox',$mb.'$(".mbox").multibox',$src);
			else
			{
				$mb='<link rel="stylesheet" type="text/css" href="'.$rel_path.'extimages/scripts/fancybox.css" media="screen" />'.F_LF
					.'<script type="text/javascript" src="'.$rel_path.'extimages/scripts/fancybox.js"></script>'.F_LF
					.'<script type="text/javascript">'.F_LF.(self::$f->xhtml_on?'/* <![CDATA[ */'.F_LF:'')
					.'$(document).ready(function(){'.$mb.'$(".mbox").multibox({zicon:true});});'.F_LF.(self::$f->xhtml_on?'/* ]]> */'.F_LF:'')
					.'</script>'.F_LF;
				$src=str_replace('<!--endscripts-->','<!--endscripts-->'.F_LF.$mb,$src);
			}
		}
		return $src;
	}

	public static function includeCss($src,$css)
	{
		$ct='<style type="text/css">';
		$cte='</style>';
		if(strpos($css,$ct)!==false)
			$css=trim(Formatter::GFS($css,$ct,$cte));
		if(strpos($src,$ct)!==false)
		{
			$pos=strpos($src,$ct);
			if($pos!==false)
				$src=substr_replace($src,$ct.F_LF.$css,$pos,strlen($ct));
		}
		else
			$src=str_replace('<!--scripts-->',$ct.F_LF.$css.F_LF.$cte.'<!--scripts-->',$src);
		return $src;
	}

	public static function includeBrowseDialog($src,$rel_path,$lang='english',$resize_chkbx=true)
	{
		if(strpos(self::$f->editor_js,'%XLANGUAGE%')!=false)
			$lang=isset(self::$f->innova_lang_list[$lang])?self::$f->innova_lang_list[$lang]:self::$f->innova_lang_list['english'];

		$sc=sprintf(self::$f->md_dialog,$rel_path,$lang,$rel_path)."function fixima(val,id){ima=document.getElementById('ima_'+id);ima.src=val;ima.style.display=(val=='')?'none':'block';};";
		if(isset($_SERVER['HTTP_USER_AGENT']))
			$ag=$_SERVER['HTTP_USER_AGENT'];
		if(!$resize_chkbx)
			$sc=str_replace('assetmanager.php?lang=','assetmanager.php?resize=0&lang=',$sc);
		if((strpos($src,'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">')!==false)&&isset($ag)&&(strpos($ag,'Internet Explorer')!==false||strpos($ag,'MSIE 8')!==false))
			$sc=str_replace('modalDialogShow','mDialogShow',$sc);
		$src=self::includeScript($sc,$src);
		return $src;
	}

	public static function includeScript($script,$page_src,$dependancies=array(),$r_path='')
	{
		if(!empty($script)&&strpos($page_src,$script)===false)
		{
			if(self::$f->xhtml_on)
				$script_enclosed='<script type="text/javascript">'.F_LF.'/* <![CDATA[ */'.F_LF.$script.F_LF.'/* ]]> */'.F_LF.'</script>'.F_LF;
			else
				$script_enclosed='<script type="text/javascript">'.F_LF.$script.F_LF.'</script>'.F_LF;

			$start='<!--endscripts-->';
			if(strpos($page_src,$start)===false)
				$start='</head>';
			$page_src=str_replace($start,$script_enclosed.$start,$page_src);
		}
		if(!empty($dependancies))
		{
			foreach($dependancies as $v)
			{
				if(strpos($v,'.css')!==false&&strpos($page_src,$v)===false)
				{
					$page_src=str_replace('<!--scripts-->','<link rel="stylesheet" type="text/css" href="'.$r_path.$v.'">'.F_LF.'<!--scripts-->',$page_src);
				}
				elseif(strpos($page_src,$v.'.js')===false)
				{
					$page_src=str_replace('<!--scripts-->','<!--scripts-->'.F_LF.'<script type="text/javascript" src="'.$r_path.$v.'.js"></script>',$page_src);
				}
			}
		}
		return $page_src;
	}

	public static function includeGFonts($src)
	{
		if(strpos(self::$f->editor_js,'%XLANGUAGE%')!=false)
		{
			$js='';
			$fonts=join("|",self::$f->gfonts);
			$matches=array();
			if(preg_match_all('/'.$fonts.'/',$src,$matches))
			{
				$matches=array_unique($matches[0]);
				foreach($matches as $v)
					if(strpos($src,'family='.$v.'" rel')==false)
						$js.='<link href="http://fonts.googleapis.com/css?family='.$v.'" rel="stylesheet" type="text/css">'.F_LF;
				if($js!='')
					$src=str_replace('</title>','</title>'.F_LF.$js,$src);
			}
		}
		return $src;
	}

	public static function appendScript($script,$page_src)
	{
		return str_replace(array('</HEAD>','</head>'),' '.$script.' </head>',$page_src);
	}

	public static function includeModalDialogJs($page_src,$path,$height='160')
	{
		return self::includeScript('$(function() {$("#dialog:ui-dialog").dialog( "destroy" );$("#dialog-modal").dialog({height:'.$height.',modal: true});});',$page_src,array('extimages/scripts/jquery_plus','extimages/scripts/jquery_plus.css'),$path);
	}

	public static function addModalDialogJs($title,$content)
	{
		return '<div id="dialog-modal" title="'.$title.'"><p>'.$content.'</p></div>';
	}

	public static function getDatepicker($field_name,$month_name,$day_name)
	{
		foreach($month_name as $k=> $v)
			$m_t[]="'".$v."'";
		$mon_impl=implode(',',$m_t);
		foreach($day_name as $k=> $v)
		{
			$d_sh[]="'".Formatter::mySubstr($v,0,2,self::$f->uni)."'";
		}
		$day_sh_impl=implode(',',$d_sh);

		$result='$(document).ready(function(){$(".'.$field_name.'").datepicker({showOtherMonths:true,changeYear:true,monthNames:['.$mon_impl.'],dayNamesMin:['.$day_sh_impl.'],dateFormat:\'MM d, yy\'});}); ';
		return $result;
	}

	public static function includeDatepicker($output,$path,$month_name,$day_name,$timepicker,$field_name,$field_name2='',$field_name3='',$field_name4='')
	{
		$js='';
		if(strpos($output,'jquery_plus.css')===false)
			$js='<link rel="stylesheet" type="text/css" href="'.$path.'extimages/scripts/jquery_plus.css">'.F_LF;
		if(strpos($output,'jquery_plus.js')===false)
			$js.='<script type="text/javascript" src="'.$path.'extimages/scripts/jquery_plus.js"></script>';

		$js.=F_LF.'<script type="text/javascript">'.F_LF;
		if($field_name!='')
			$js.=self::getDatepicker($field_name,$month_name,$day_name).F_LF;
		if($field_name2!='')
			$js.=self::getDatepicker($field_name2,$month_name,$day_name).F_LF;
		if($field_name3!='')
			$js.=self::getDatepicker($field_name3,$month_name,$day_name).F_LF;
		if($field_name4!='')
			$js.=self::getDatepicker($field_name4,$month_name,$day_name).F_LF;
		$js.='</script>'.F_LF;

		$output=str_replace(array('</HEAD>','</head>'),F_LF.$js.'</head>',$output);
		return $output;
	}

	public static function buildRanking($rank_value,$entry_id,$page_id)
	{
		return '<span class="ranking">'.$rank_value.':'.$entry_id.':'
			.(Cookie::entryIsCookie($entry_id,$page_id,'ranking_')?0:(self::$f->direct_ranking?2:1)).'</span>';
	}

	public static function buildAdminRanking($ranking_voted,$ranking_total,$ct_color,$label)
	{
		$space=3;
		$w=5;
		$output='';
		if($ranking_voted>0)
		{
			$output='<div style="position:relative;width:200px;height:14px;">';
			if(self::$f->ranking_average)
			{
				$score=($ranking_total/$ranking_voted);
				$r_main=floor($score);
				for($i=0; $i<$r_main; $i++)
					$output.='<div style="position:absolute;width:'.$w.'px;left:'.($i*($w+$space)).'px;bottom:2px;height:10px;background:'.$ct_color.';">&nbsp;</div>';
				$r_reminder=($ranking_total%$ranking_voted);
				if($r_reminder==1)
					$r_reminder=2;
				if($r_reminder!=0)
					$output.='<div style="position:absolute;width:'.ceil($r_reminder/2).'px;left:'.(($r_main)*($w+$space)).'px;bottom:2px;height:10px;background:'.$ct_color.';">&nbsp;</div>';

				$output.='<div class="rank_text" style="position:absolute;width:160px;left:42px;bottom:0px;"><span class="rvts8">'
					.round($score,1).' \\ '.$ranking_voted.' '.$label.'</span></div></div>';
			}
			else
			{
				$score=$ranking_total;
				$r_main=floor($score);
				$output.='<div style="position:absolute;width:'.$r_main.'px;left:25px;bottom:2px;height:10px;background:'.$ct_color.';">&nbsp;</div>';
				$output.='<div class="rank_text" style="position:absolute;left:2px;bottom:0px;"><span class="rvts8">'
					.round($score,1).'</span></div></div>';
			}
		}
		return $output;
	}
	public static function tooltip($url,$class,$title,$text,$imgpath,$link,$im_height='',$im_width='',$more='')
	{
		$hint_id=($text!='')?'hhint':'ihint';
		$style=($im_height!='')?'height:'.$im_height.'px;':'';
		$style.=($im_width!='')?'width:'.$im_width.'px;':'';
		$style=($style!='')?' style=&quot;'.$style.'&quot;':'';
		$text=($text==''&&$imgpath!='')?'&lt;img alt=&quot;&quot; src=&quot;'.$imgpath.'&quot;'.$style.'&gt;':$text;
		$result='<a href="'.$url.'" class="'.$hint_id.($class!=''?' '.$class:'').'" title="'.$title.'::'.$text.'" '.$more.'>'.$link.'</a>';
		return $result;
	}

	public static function getEntryTableRows($tabledata,$script_name='')
	{
		$r='';
		foreach($tabledata as $value)
		{
			$r.='<tr class="'.self::$f->atbg_class.'"><td>';
			if(is_array($value))
			{
				$cnt=count($value);
				if($cnt>2)
				{
					foreach($value as $k=> $v)
					{
						if(!Unknown::isOdd($k))
						{
							$ctrl=$value[$k+1];
							$r.='<div '.($ctrl==''?'':'style="display:inline;float:left;padding-right:5px;').'">';
							if($value[$k]!='')
								$r.=sprintf(self::$f->ftm_title,$value[$k]);
							$r.=$ctrl;
							$r.='</div>';
						}
					}
					$v='';
				}
				else
				{
					if($value[0]!='')
						$r.=sprintf(self::$f->ftm_title,$value[0]);
					$r.=$value[1];
				}
			}
			else
				$r.=$value;
			$r.='</td></tr>';
		}

		if($script_name!='')
		{
			$act_param=strpos($script_name,'centraladmin.php')!==false?'process':'action';
			$r.='<script type="text/javascript">$(document).ready(function(){'
				.'$(".ui_shandle_ic3").click(function(){
					var pp=$(this).parent(),rel=$(this).attr("rel");
					$.post("'.$script_name.'?'.$act_param.'=fvalues&fid="+rel,function(data){
						ar=data.split("#");
						$(".xsel").remove();
						var s=$("<select />");
						s.addClass("input1 xsel");
						for(v in ar) {
							subar=ar[v].split("<><><>");
							$("<option />",{value:subar[0],text:subar[1]}).appendTo(s);
						};
						s.change(function(){$("input[name="+rel+"]").val($(this).val());});
						s.appendTo(pp);
					});
				});'
				.'});</script>';
		}
		return $r;
	}

	public static function getEntryTableRowsDrag($tabledata,$sort,$script_name)
	{
		$hover_class='ui_shandle_highlight';//self::$f->atbgr_class=='t3'?'t3_caption':'ui_shandle_highlight';

		$sort_a=array_keys($tabledata);
		$dis=array();
		if($sort!='')
		{
			$s=explode('-',$sort);
			if(isset($s[1]))
				$dis=explode('|',$s[1]);
			if($s[0]!='')
			{
				$sort_a=explode('|',$s[0]);
				if($sort_a[count($sort_a)-1]=='')
					array_pop($sort_a);
				foreach($tabledata as $k=> $v)
				{
					if(array_search($k,$sort_a)===false)
						$sort_a[]=strval(count($sort_a));
				}
				$temp=array();
				foreach($sort_a as $k=> $v)
					if(isset($tabledata[$v]))
						$temp[]=$tabledata[$v];
				$tabledata=$temp;
			}
		}

		$r='<tr><td><ul id="sort_table" style="list-style:none">';
		foreach($tabledata as $key=> $row)
		{
			$ihidden=$row[0]==='hidden';
			$merged=$row[0]==='merged';
			$draggable=!$ihidden&&$row[0]!==false;
			$ver=$row[0]==='ver';
			$title=$row[1];
			$single=!(count($row)>5);
			$rowid=$sort_a[$key];
			$row_visible=$row[0]==false||(is_array($dis)&&array_search($rowid,$dis)===false);
			$r.='<li id="sort_'.$rowid.'" class="'.self::$f->atbg_class.'" style="'.($ihidden?'display:none;':'').($merged?'':'margin-bottom:2px;').'">
				<div style="clear:left;padding:4px;position:relative;">';
			if($title!='')
				$r.='<div class="ui_shandle"><span class="rvts8 a_editcaption">'.$title.'</span>'.($draggable?'<a class="ui_shandle_ic1"></a><a class="ui_shandle_ic2"></a>':'').'</div>';
			$r.='<div class="ui_sdata"'.($row_visible?'':' style="display:none"').'>';

			foreach($row as $k=> $v)
			{
				if($k>1&&!Unknown::isOdd($k))
				{
					$r.='<div'.($single||$ver?'':' style="display:inline;float:left;padding-right:5px;"').'>';
					if($v!='')
						$r.=sprintf(self::$f->ftm_title,$v);
					if(isset($row[$k+1]))
						$r.=$row[$k+1].'</div>';
				}
			}
			$r.='</div>';
			if(!$single)
				$r.='<div style="clear:left"></div>';
			$r.='</div></li>';
		}
		$r.='</ul></td></tr>';

		$r.='
			<script type="text/javascript">
			$(document).ready(function(){
			$(".ui_shandle").hover(function(){$(this).addClass("'.$hover_class.'");},function(){$(this).removeClass("'.$hover_class.'");});
			$(".ui_shandle_ic1").click(function(){$(this).parent().next().toggle();id=($(this).parent().next().is(":visible")?"+":"-")+$(this).parents("li").attr("id").substr(5);$.post("'.$script_name.'",{"toggle":id})});
			$("#sort_table").sortable({handle:".ui_shandle_ic2",placeholder:"ui-state-highlight",update:function(){$.post("'.$script_name.'",$("#sort_table").sortable("serialize") )}});
			$(".ui_shandle_ic3").click(function(){var pp=$(this).parent(),rel=$(this).attr("rel");$.post("'.$script_name.'?action=fvalues&fid="+rel,function(data){d=data="---#"+data;ar=d.split("#");$(".xsel").remove();var s=$("<select />");s.addClass("input1 xsel");for(v in ar) {$("<option />",{value:ar[v],text:ar[v]}).appendTo(s);};s.change(function(){$("input[name="+rel+"]").val($(this).val());});s.appendTo(pp);} ) });
			});
			</script>';

		return $r;
	}

	public static function addEntryTable($tabledata,$apend='',$tag='',$prepend='',$addhandle=false,$sort='',$script_name='',$frm='',$frmend='</form>')
	{

		$output='<script type="text/javascript">function s_roll(id,tg){document.getElementById(id).style.visibility=(tg)?"visible":"hidden"};</script>';
		if($prepend!=='')
			$output.=self::$f->navtop.$prepend.self::$f->navend.'<br class="ca_br" />';

		if($frm!=='')
			$output.=$frm;
		$output.=str_replace('a_navt','a_navn',self::$f->navlist).'<table class="atable '.self::$f->atbgr_class.'" cellspacing="1" cellpadding="3" '.$tag.'>';
		if($addhandle)
			$output.=self::getEntryTableRowsDrag($tabledata,$sort,$script_name);
		else
			$output.=self::getEntryTableRows($tabledata,$script_name);
		if($apend!='')
			$output.='<tr><td>'.$apend.'</td></tr>';
		$output.='</table>';
		$output.=self::$f->navend;
		if($frm!=='')
			$output.=$frmend;
		return $output;
	}

	public static function adminTable($page_nav,$captions,$tabledata,$apend='',$tag='',$sort='',$form_around_table=array())
	{

		$table_pre='';
		$table_post='';
		if(count($form_around_table)>0)
		{
			$table_pre='<form ';
			foreach($form_around_table AS $fk=> $fv)
				$table_pre.=$fk.'="'.$fv.'" ';
			$table_pre.='>';
			$table_post='</form>';
		}
		$sort_a=array_keys($tabledata);
		if($sort!='')
		{
			$sort_a=explode('|',Formatter::GFS($sort,'','-'));
			if($sort_a[count($sort_a)-1]=='')
				array_pop($sort_a);
			foreach($tabledata as $k=> $v)
			{
				if(array_search($k,$sort_a)===false)
					$sort_a[$k]=strval(count($sort_a));
			}
			$temp=array();
			foreach($sort_a as $k=> $v)
				if(isset($tabledata[$v]))
					$temp[]=$tabledata[$v];
			$tabledata=$temp;
		}

		$cs=count($captions);
		if($cs==0)
			$cs=1;

		$table='<table class="atable '.self::$f->atbgr_class.' a_left" cellspacing="1" cellpadding="4" '.$tag.(empty($tabledata)?' width="500px"':'').'>';
		if(!empty($captions))
		{
			$table.='<tr class="'.self::$f->atbgr_class.'">';
			foreach($captions as $key=> $value)
			{
				if(is_array($value))
				{
					$table.='<td>'.sprintf('<a class="a_tabletitle" href="%s" style="text-decoration:%s">%s</a>',$value[0],$value[1],$value[2]);
					if(isset($value[3]))
						$table.=$value[3];
					for($i=4; $i<=10; $i++)
					{
						if(isset($value[$i]))
						{
							if(is_array($value[$i]))
								$table.=sprintf('<a class="a_tabletitle extra" href="%s" style="margin-left: 10px; text-decoration:%s">%s</a>%s',$value[$i][0],$value[$i][1],$value[$i][2],$value[$i][3]);
							else
								$table.=$value[$i];
						}
					}
					$table.='</td>';
				}
				else
					$table.='<td>'.sprintf(self::$f->fmt_caption,$value).'</td>';
			}
			$table.='</tr>'.F_LF;
		}
		$i=1;
		if(!empty($tabledata))
		{
			foreach($tabledata as $key=>$row_data)
			{
				$row='';
				$j=0;
				$hglt_row=is_array($row_data);
				if($hglt_row)
				{
					foreach($row_data as $col_data)
					{
						if(is_array($col_data))
						{
							$row.='<td>';
							$j++;
							$row.=$col_data[0].'<div class="a_detail" id="aa'.$j.'_'.$i.'">';
							if(is_array($col_data[1]))
								foreach($col_data[1] as $key3=> $col_links)
									if(is_array($col_links)&&count($col_links)>1)
										$row.='<span class="rvts8">[</span><a class="'.$col_links['class'].' rvts12" '.$col_links['extra_tags'].' href="'.$col_links['url'].'">'.$key3.'</a><span class="rvts8">]</span> ';
									else
										$row.='<span class="rvts8">[</span><a class="rvts12" href="'.$col_links.'">'.$key3.'</a><span class="rvts8">]</span> ';
							$row.='</div>';
						}
						else
						{
							$style='';
							if(strpos($col_data,'rel="cc:')!==false)
							{
								$ct_color=Formatter::GFS($col_data,'rel="cc:','"');
								$style=' style="border-right: 5px solid '.$ct_color.';"';
							}

							$row.='<td'.$style.'>'.$col_data;
						}
						$row.='</td>';
					}
				}
				else
					$row.='<td colspan="'.$cs.'">'.$row_data.'</td>';

				$row.='</tr>'.F_LF;
				$xclass=Unknown::isOdd($i)?' odd':' even';
				if($hglt_row)
				{
					$table.='<tr class="'.self::$f->atbg_class.$xclass;
					if($j>0)
					{
						$table.='" onmouseover="';
						for($w=1; $w<=$j; $w++)
							$table.='s_roll(\'aa'.$w.'_'.$i.'\',1,this,\''.self::$f->atbgc_class.'\');';
						$table.='" onmouseout="';
						for($w=1; $w<=$j; $w++)
							$table.='s_roll(\'aa'.$w.'_'.$i.'\',0,this,\''.self::$f->atbg_class.'\');';
					}
				}
				else
					$table.='<tr class="'.self::$f->atbgc_class.$xclass;
				$table.='">'.$row;
				$i++;
			}
		}
		if($apend!='')
			$table.='<tr><td colspan="'.$cs.'">'.$apend.'</td></tr>'.F_LF;
		$table.='</table>';

		$output='<script type="text/javascript">
			var act=null;
			function s_roll(id,tg,th,cn){
				if(act==null){th.className=cn;document.getElementById(id).style.visibility=(tg)?"visible":"hidden"}
			};
   		$(document).ready(function(){
			 		$(".row_hidden").parents("tr").hide();
			});
			</script>'.F_LF
			.($page_nav!==''?'<div class="a_n a_navtop"><div class="a_navt">'.$page_nav.'</div></div><br class="ca_br" />':'')
			.'<div class="a_n a_listing"><div class="a_navn">'
			.$table_pre
			.$table
			.$table_post
			.($i>11&&$page_nav!=''?'<div class="a_navt a_foot">'.$page_nav.'</div>':'')
			.'</div></div>';
		return $output;
	}

	public static function getCountriesArray($first_item)
	{
		$res=array_merge(array('Select'=>$first_item),self::$f->countries_list);
		return $res;
	}

	public static function getCategoryInfo($category_name,$category_color,$category_id,$search_category,$flag)
	{
		settype($search_category,"integer");
		if(in_array($search_category,$category_id))
		{
			$buf=array_search($search_category,$category_id);
			$cat_res=($flag=='name')?Formatter::unEsc($category_name[$buf]):$category_color[$buf];
		}
		else
		{
			$cat_res=($flag=='name')?Formatter::unEsc($category_name[array_search(1,$category_id)]):$category_color[array_search(1,$category_id)];
		}
		return $cat_res;
	}

	# builds logged user menu (logout, edit profile), represented in EZG with %LOGGED_INFO% macro

	public static function buildLoggedInfo($content,$page_id,$root_path,$script_path,$lg='')
	{
		global $user;
		if(Unknown::strpos_multi($content,array('%USER_COUNT%','%GUEST_COUNT%','%USERS%')))
			$content=$user->getOnlineUsers($content);

		$code='%LOGGED_INFO';
		$content=str_replace(
				array($code.'%',"<?php if(function_exists('user_navigation')) user_navigation(); ?>"),
				$code.'()%',
				$content);

		if(strpos($content,$code)!==false)
		{
			$labels=CA::getMyprofileLabels($page_id,$root_path);
			Session::intStart();
			$logged_as_admin=Cookie::isAdmin();
			$logged_as_user=$user->userCookie();
			$logged=$logged_as_user||$logged_as_admin;
			if($logged)
			{
				$pageid_info=CA::getPageParams($page_id,$root_path);

				$logged_user=$logged_as_admin?self::$f->admin_nickname:$user->getUserCookie();
				while(strpos($content,$code)!==false)
				{
					$params_raw=Formatter::GFSAbi($content,$code,')%');
					if($params_raw!='')
					{
						$logged_info='';
						$params=Formatter::GFS($params_raw,'(',')');
						$params=explode(',',str_replace("'",'',$params));
						if(Formatter::strToLower(implode('',$params))=="username"||(isset($params[0])&&$params[0]=='true'))
							$logged_info=$logged_user;
						else
						{
							$captions=array();
							$urls=array();
							$ca_url=$root_path.((strpos($root_path,'documents')===false)?'documents/':'').'centraladmin.php?';
							$captions[]=strpos($labels['welcome'],'%%username%%')===false?$labels['welcome'].' ['.$logged_user.']':str_replace('%%username%%',$logged_user,$labels['welcome']);
							$urls[]='';
							if($logged_as_admin)
							{
								if(isset($pageid_info[4])&&in_array($pageid_info[4],self::$f->sp_pages_ids))
								{
									$captions[]=$labels['edit'];
									$urls[]=CA::defineAdminLink($pageid_info);
								}
								$captions[]=$labels['administration panel'];
								$urls[]=$ca_url.'process=index&amp;'.$lg;
								$captions[]=$labels['logout'];
								$urls[]=$ca_url.'process=logoutadmin&amp;pageid='.$page_id.'&amp;'.$lg;
							}
							else
							{
								if(isset($pageid_info[4])&&in_array($pageid_info[4],self::$f->sp_pages_ids)&&$user->hasWriteAccess($logged_user,$pageid_info,$root_path))
								{
									$captions[]=$labels['edit'];
									$urls[]=CA::defineAdminLink($pageid_info);
								}
								$ca_expanded_url=$ca_url.'&amp;username='.$logged_user.'&amp;pageid='.$page_id.'&amp;ref_url='.urlencode($script_path).'&amp;process=';
								$captions[]=$labels['profile'];
								$urls[]=$ca_expanded_url.'editprofile&amp;'.$lg;
								$captions[]=$labels['logout'];
								$urls[]=$ca_url.'process=logout&amp;pageid='.$page_id.'&amp;'.$lg;
							}
							$logged_info=Navigation::user($captions,$urls);
						}
						$content=str_replace($params_raw,$logged_info,$content);
					}
				}
			}
			else
				$content=str_replace(Formatter::GFSAbi($content,$code,')%'),'',$content);
		}

		if(strpos($content,'%LOGGED')!==false)
		{
			Session::intStart();
			$logged_as_admin=Cookie::isAdmin();
			$logged_as_user=$user->userCookie();
			$logged=$logged_as_user||$logged_as_admin;
			$logged_name='';
			if($logged)
				$logged_name=$logged_as_user?$user->getUserCookie():self::$f->admin_nickname;
			$content=str_replace(array('%LOGGED%','%LOGGED_USER%'),array($logged,$logged_name),$content);
			if(strpos($content,'%LOGGED_')!==false) //parse other user params if needed
			{
				$user_data=$user->mGetLoggedValues($root_path,self::$f->db);
				foreach($user_data as $k=> $v)
					if(!is_array($v)&&$k!='password')
					{
						if($k=='avatar')
							$v=($v!='')?'<img class="system_img" src="'.$root_path.$v.'" alt="" style="height:'.self::$f->avatar_size.'px;padding-left:5px;">':'';
						$content=str_replace('%LOGGED_'.$k.'%',$v,$content);
					}
				if(!$logged_as_user)
					$content=preg_replace('/\%LOGGED_\w+\%/',"",$content);
			}
		}

		return $content;
	}

	public static function getDirectEditJS($container_class,$script_path,$ignore_parents=false)
	{
		return '
	function closeTL(th){$(".ui_hidden").show();$(th).parent().find(".ui").remove();};
	function deleteC(th,idt,idp){$.get("'.$script_path.'",{action:\'del_comment\',\'cc\':1,comment_id:idt,entry_id:idp},function(){$(th).closest(".blog_comments_entry").remove();});}
	function updateTL(th,rel){
		var idt=$(th).prev().attr("name"),dt=$(th).prev().val(),rl=$(th).parent().attr("rel");
		$.get("'.$script_path.'",{action:\'updatetl\',id:idt,data:dt,rel:rl},function(r){
		$(".ui").remove();if(rel) $("."+idt).html(r).attr("rel",dt);else $("."+idt).html(dt);$(".ui_hidden").show();
		});}
	function editTL(id){
		var rel=$("."+id).hasClass("el_rel"),html=rel?$("."+id).attr("rel"):$("."+id).html(),cc=$(".'.$container_class.' ."+id);'.
			($ignore_parents?'':'if(cc.parents("a").length>0) cc=cc.parents("a");').
			'cc.after(\'<input class="ui ui_input" onclick="return false" type="text" name="\'+id+\'" value="\'+html+\'"><input type="button" onclick="updateTL(this,\'+rel+\');" class="ui ui_shandle_ic4"><input type="button" onclick="closeTL(this);" class="ui ui_shandle_ic5">\');
		$(".ui_shandle_ic5").next().addClass("ui_hidden").hide();
	};
	';
	}

	public static function getDirectEditCSS($rel_path)
	{
		return '.ui_hidden{display:none;}.ui_shandle_ic6,.ui_shandle_ic5,.ui_shandle_ic4{background-color:#fff;background-image: url("'.$rel_path.'extimages/scripts/ui-icons.png");background-position: -64px -144px;border: medium;border-radius:2px;cursor: pointer;height: 16px;margin-left: 2px;width: 16px;}
			.ui_shandle_ic5{background-position: -80px -368px;}.ui_shandle_ic6{background-position: -176px -352px;}.ui_input{width:90%}';
	}

	public static function printImgHtml($rel_path)
	{
		return '<img class="system_img" src="'.$rel_path.'ezg_data/print.png" alt="Print" style="vertical-align: middle;">';
	}

	public static function detailedStat($timestamp,$page_id,$uniq_flag,$firsttime_flag,$q='',$rc=0)
	{

		$frames_mode=(isset($_GET['frames'])&&$_GET['frames']=='1');
		$stat=array();
		$stat['page_id']=$page_id;
		$stat['timestamp']=$timestamp;

		$stat['ip']=Detector::getIP();
		$stat['host']=Detector::getRemoteHost();
		if($stat['ip']==$stat['host'])
			$stat['host']='';

		$agent=Detector::readUserAgent(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'',$stat['host']);
		$stat['browser']=(isset($agent['browser'])?$agent['browser']:'');
		$stat['os']=(isset($agent['platform'])?$agent['platform']:'');
		$stat['resolution']=($q!=''?$rc:(isset($_GET['w'])&&isset($_GET['h'])?intval($_GET['w']).'x'.intval($_GET['h']):''));

		if($q!='')
			$stat['referrer']='documents/search.php?q='.$q;
		else
		{
			$stat['referrer']='NA';
			$http_ref=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'NA';
			if(!$frames_mode)
				$http_ref=isset($_REQUEST['referrer'])?Formatter::stripTags($_REQUEST['referrer']):$http_ref;
			if($http_ref!=='NA')
			{
				$h=Detector::getRemoteHost();
				$stat['referrer']=(strpos($http_ref,$h)===0)?substr($http_ref,strpos($http_ref,$h)+strlen($h)):$http_ref;
			}
		}
		$stat['type']=(!$uniq_flag?'h':($firsttime_flag?'f':'r') );

		return $stat;
	}

	//$items_list param must be an array and must has ['email'] index (column)
	public static function doubleSelector($items_list,$left_caption,$right_caption,$left_select_id,$right_select_id,$preselected_items_list=array())
	{

		$table="<table><tr><td>".sprintf(self::$f->fmt_caption,$left_caption).F_BR
			.'<select id="'.$left_select_id.'" class="input1" multiple size="20" style="width:230px" name="'.$left_select_id.'[]">';
		foreach($items_list as $k=> $v)
		{
			$em=Formatter::sth($v['email']);
			$em2=$v['uid'];
			if(!in_array($em,$preselected_items_list)&&!empty($em))
				$table.='<option value="'.$em2.'">'.$em.'</option>';
		}
		$table.= '</select></td><td><br><input name="right" type="button" value="  >>  " onclick="moveright();">'
			.F_BR.'<input name="left" type="button" value="  <<  " onclick="moveleft();">'
			.F_BR.F_BR.'<input name="all" type="button" value="*>>" onclick="moverightAll();"></td>';
		$table.= "<td>".sprintf(self::$f->fmt_caption,$right_caption)
			.F_BR.'<select id="'.$right_select_id.'" multiple class="input1" size="20"'
			.' style="width:230px" name="'.$right_select_id.'[]">';
		foreach($preselected_items_list as $k=> $v)
		{
			if(!empty($v))
				$table.='<option value="'.$k.'">'.Formatter::sth($v).'</option>';
		}
		$table.='</select></td></tr></table>';

		return $table;
	}

	public static function getDoubleSelectorScript($left_select_id,$right_select_id)
	{
		return 'function moveleft() {'
			.'l=$("#'.$left_select_id.'")[0];r=$("#'.$right_select_id.'")[0];'
			.'var j=0;if(l.options.length>0) j=l.options.length;'
			.'for(i=0;i<r.options.length;i++) {if(r.options[i].selected) {l.options[j]=new Option(r.options[i].text,r.options[i].value);j++;}}'
			.'for(m=r.options.length-1;m>=0;m--) {if(r.options[m].selected) r.options[m]=null;} '
			.'}; '
			.'function moveright() {'
			.'l=$("#'.$left_select_id.'")[0];r=$("#'.$right_select_id.'")[0];'
			.'var j=0;if(r.options.length>0) j=r.options.length;'
			.'for(i=0;i<l.options.length;i++) {if(l.options[i].selected) '
			.'{r.options[j]=new Option(l.options[i].text,l.options[i].value);r.options[j].selected=true;j++;} }'
			.'
		for(m=l.options.length-1; m>=0; m--) {
		if(l.options[m].selected)
		l.options[m]=null;
		}'
			.'}; '
			.'function moverightAll() {'
			.'markl();moveright();'
			.'}; '
			.'function markl(){el=$("#'.$left_select_id.'")[0];for(i=0;i<el.options.length;i++) el.options[i].selected=true;};'
			.'function mark(){el=$("#'.$right_select_id.'")[0];for(i=0;i<el.options.length;i++) el.options[i].selected=true;};'
			.'function toggle_admin_check(el){var target=$(el).parents("tr").next();if($(el).is(":checked")) target.hide();else target.show();};'
			.'$(document).ready(function(){	$("#usr_grps_select").change(function(){var val= $(this).find(":selected").val();'
			.'var grp=val.split(",");var el=$("#emails_select")[0];for(i=0;i<el.options.length;i++) {var res= $.inArray(el.options[i].value,grp);'
			.'if(res!=-1) el.options[i].selected=true;} moveright();})});';
	}

	public static function hChart($data,$width,$height,$offs=110,$show_vals=false,$sort=true,$zero_tot=false)
	{
		if(!is_array($data)||count($data)==0)
			return '';
		if($sort)
			ksort($data);
		$grid=true;
		$width-=$offs;
		$grid_w=$width/10;
		$colors=array('a_chart_color1','a_chart_color2');
		$ret='<div class="hchart" style="width:'.($width+$offs).'px;height:'.$height.'px;">'.F_LF;
		$h=$height/count($data);
		if($grid)
		{
			for($i=0; $i<11; $i++)
			{
				$ret.='<div class="hchart_line" style="height:'.$height.'px;left:'.(($i*$grid_w)+$offs).'px;"></div>'.F_LF;
				$ret.='<div class="hchart_line_point" style="left:'.(($i*$grid_w)+$offs).'px;"></div>'.F_LF;
			}
		}

		$col_cnt=count($colors);
		$t=0;
		$md=(max($data)==0?1:max($data));
		$tot=0;
		foreach($data as $v)
			$tot+=$v;
		if($tot!=0||$zero_tot)
		{
			$i=0;
			foreach($data as $k=> $v)
			{
				$color=$colors[$i%$col_cnt];
				$pc=($tot==0?0:($v/$tot)*100);
				$ret.='<div class="hchart_data '.$color.'" style="width:'.($v/$md*($width)).'px;height:'.($h-1).'px;top:'.$t.'px;left:'.$offs.'px;">'.($show_vals?'<span style="margin-left: 10px">'.$v.'</span>':'').'</div>'.F_LF;
				if(strlen($k)>40)
				{
					$tooltip_k=$k;
					if(strpos($k,'<img')!==false)
						$k=str_replace(Formatter::GFSAbi($k,'<img','>'),'[+]',$k);
					if(strlen($k)>40)
						$k=substr($k,0,40).'...';
					$span='<span class="show-tooltip" title="'.$tooltip_k.'">'.$k.'</span>';
				}
				else
					$span='<span>'.$k.'</span>';
				$ret.='<div class="hchart_labels" style="top:'.($t+2).'px;">'.$span.'</div>'.F_LF;
				$ret.='<div class="hchart_labels_pc" style="left:'.($offs-45).'px;top:'.($t+2).'px;"><span>'.number_format($pc,1).'%</span></div>'.F_LF;
				$t+=$h;
				$i++;
			}
		}
		$ret.='</div>'.F_LF;
		return $ret;
	}

	public static function vChart($data,$width,$height,$labels,$ye)
	{
		if(!is_array($data)||count($data)==0)
			return '';
		if(!isset($_REQUEST['f']))
			$_REQUEST['f']='h';
		$colors=array('a_chart_color1','a_chart_color2');
		$cd=count($data);
		$link=Linker::buildSelfURL('centraladmin.php').'?process=index&stat=detailed';
		$link.=(!isset($_REQUEST['pid']))?'&f='.$_REQUEST['f'].'&mo=':'&pid='.$_REQUEST['pid'].'&f='.$_REQUEST['f'].'&purl='.$_REQUEST['purl'].'&pname='.$_REQUEST['pname'].'&mo=';
		$ret='<div class="vchart" id="'.($ye?'year_chart':'month_chart').'" style="width:'.$width.'px; height:'.$height.'px;">';
		$w=max(1,floor(($width-$cd)/$cd));
		$width=($w+1)*$cd;
		$grid_h=$height/10;
		for($i=0; $i<11; $i++)
		{
			$ret.='<div class="vchart_line" style="width:'.$width.'px;top:'.$i*$grid_h.'px"></div>';
			$ret.='<div class="vchart_line_point" style="top:'.$i*$grid_h.'px"></div>';
		}
		$i=0;
		$col_cnt=count($colors);
		$md=max($data);
		foreach($data as $v)
		{
			$color=$colors[$i%$col_cnt];
			$js=($ye)?' onclick="document.location=\''.$link.($i+1).'\'"':'';
			if($v!=0)
				$ret.='<div'.$js.' class="vchart_data '.$color.'" style="width:'.($w-1).'px;height:'.($v/$md*$height).'px;left:'.($i*$w+$i).'px;"></div>';
			if($v!=0)
				$ret.='<div class="vchart_data_text" style="width:'.($w).'px;left:'.($i*$w+$i).'px;bottom:'.(($v/$md*$height)+1).'px;"><span>'.$v.'</span></div>';
			$i++;
		}
		$i=0;
		foreach($labels as $v)
		{
			$ret.='<div class="vchart_labels" style="width:'.($w).'px;left:'.($i*$w+$i).'px;"><span>'.$v.'</span></div>';
			$i++;
		}
		$ret.='</div>'.F_BR.F_BR;
		return $ret;
	}

	public static function ipLocator($ip)
	{
		return '<a class="rvts12" style="text-decoration:none;" href="http://en.utrace.de/?query='.$ip.'" target="_blank">'.$ip.'</a>';
	}

	public static function buildInput($name,$value,$style='',$max_len='',$type='text',$misc='',$frmid='',$label='',$btn_id='')
	{

		if($type=='textarea')
			$output='<textarea name="'.$name.'" ';
		else
			$output='<input class="input1" type="'.$type.'" name="'.$name.'" value="'.str_replace('"','&quot;',$value).'" ';
		if(!empty($label))
			$output='<p><span class="rvts8 a_editcaption" style="line-height:16px">'.$label.'</span><p>'.$output;
		if(!empty($style))
			$output.='style="'.$style.'" ';
		if(!empty($max_len))
			$output.='maxlength="'.$max_len.'" ';
		if(!empty($misc))
			$output.=$misc.' ';
		if($type=='textarea')
			$output.='>'.str_replace('"','&quot;',$value).'</textarea>';
		else
			$output.='>';
		if(!empty($frmid))
			$output.='<span class="rvts12 frmhint" id="'.$frmid.'_'.$name.'"></span>';
		if($btn_id!='')
			$output='<div class="input_wrap">'.$output.'<a class="ui_shandle_ic3" rel="'.$btn_id.'"></a></div>';
		return $output;
	}

	public static function buildCheckbox($name,$checked,$caption,$class='',$id='')
	{
		$output='<input '.($id!=''?'id="'.$id.'" ':'').'class="forminput'.($class!=''?' '.$class:'').'" type="checkbox" name="'.$name.'" value="1" '.($checked=='1'?' checked="checked" ':'').' > <span class="rvts8 a_editcaption">'.$caption.'</span>';
		return $output;
	}

	public static function buildSelect($name,&$data,$selected,$style='',$mode='key',$jstring='',$class=' class="input1"')
	{
		return self::buildSelect2($name,$name,$data,$selected,$style,$mode,$jstring,$class);
	}

	public static function buildSelect2($name,$id,&$data,$selected,$style='',$mode='key',$jstring='',$class=' class="input1"')
	{
		$r='';
		if(is_array($data)&&!empty($data))
		{
			$r='<select'.$class.' '.$jstring.' '.$style.' id="'.$id.'" name="'.$name."\">";
			foreach($data as $k=> $v)
			{
				$k=($mode=='value'?$v:$k);
				if($mode=='swap')
				{
					$tmp=$k;
					$k=$v;
					$v=$tmp;
				}
				$r.='<option value="'.$k.'"';
				if($k==$selected)
					$r.=' selected="selected"';
				$r.='>'.$v.'</option>';
			}
			$r.='</select>';
		}
		return $r;
	}

	public static function buildTagCloud($script_path,$all_records,$max_tags=50,$style='',$ccloud=false,$use_flash=false,$use_alt_urls=false,$min_occs=-1,$alpha_cols=0,$px=true)
	{
		$output='';
		$tags_list=array();
		$max_font_size=$px?24:200;
		$min_font_size=$px?13:80;
		$action=$ccloud?'category':'tag';

		if($ccloud)
			$tags_list=$all_records;
		else
			foreach($all_records as $k=> $v)
			{
				$tags_per_record=explode(',',(urldecode(isset($v['Keywords'])?$v['Keywords']:$v['keywords'])));
				foreach($tags_per_record as $tag)
				{
					if($tag!='')
					{
						$tr_tag=Formatter::strToLower(trim($tag));
						if($tr_tag!==''&&array_key_exists($tr_tag,$tags_list))
							$tags_list[$tr_tag]=$tags_list[$tr_tag]+1;
						else
							$tags_list[$tr_tag]=1;
					}
				}
			}
		if($min_occs>1)
		{
			foreach($tags_list as $tname=> $tcount)
				if($tcount<$min_occs)
					unset($tags_list[$tname]);
		}
		if(!empty($tags_list))
		{
			if((count($tags_list)>$max_tags))
			{
				arsort($tags_list);
				$tags_count=0;
				$new_tags_list=array();
				foreach($tags_list as $k=> $v)
				{
					$new_tags_list[$k]=$v;
					$tags_count++;
					if($max_tags<$tags_count)
						break;
				}
				$tags_list=$new_tags_list;
			}
			$max_freq=max(array_values($tags_list));
			$min_freq=min(array_values($tags_list));
			$diff=$max_freq-$min_freq;
			if(!$px&&$diff<3)
				$diff=3;
			elseif($diff<1)
				$diff=1;
			ksort($tags_list);

			$step=$diff>0?($max_font_size-$min_font_size)/$diff:1;

			$output='';
			if($alpha_cols>0)  //aplhabetical list
			{
				$tcnt=count($tags_list);
				$tags=array();
				foreach($tags_list as $tag=> $cnt)
				{
					$l=mb_substr($tag,0,1,'UTF-8');
					if(!isset($tags[$l]))
						$tags[$l]=array();
					$tags[$l][$tag]=$cnt;
				}
				$tcnt+=count($tags);
				$colmax=round($tcnt/$alpha_cols);
				$w='position:relative;float:left;width:'.round(100/$alpha_cols).'%;';
				$icnt=0;
				$ul_open=true;
				$output.='<li class="tcloud_column" style="'.$w.'"><ul>';
				foreach($tags as $l=> $la)
				{
					$icnt++;
					$output.='<li class="alpha tcloud_head"><span>'.$l.'</span></li>';

					foreach($la as $tag=> $cnt)
					{
						$tag_enc=htmlspecialchars(stripslashes($tag),ENT_QUOTES);
						$output.='<li class="alpha tcloud_line"><a href="'.$script_path.($use_alt_urls?'/'.$action.'/':$action.'=').urlencode($tag).($use_alt_urls?'/':"").'" title="'.$tag_enc.'('.$cnt.')">'.$tag_enc.'</a> </li>';
						$icnt++;
						if($colmax<=$icnt)
						{
							$icnt=0;
							$output.='</ul></li><li class="tcloud_column" style="'.$w.'"><ul>';
							$ul_open=true;
						}
					}
				}
				$output.='</ul></li>';
			}
			else
				foreach($tags_list as $k=> $v)
				{
					if($k!=='')
					{
						if($px)
							$size=((($max_font_size-$min_font_size)/$diff)*($v-$min_freq))+$min_font_size;
						else
							$size=round($min_font_size+(($v-$min_freq)*$step));
						$tag_enc=htmlspecialchars(stripslashes($k),ENT_QUOTES);

						if(!$use_flash)
							$output.='<li><a '.$style.' href="'.$script_path.($use_alt_urls?'/'.$action.'/':$action.'=').urlencode($k).($use_alt_urls?'/':"").'" style="font-size:'.$size.($px?'px':'%').';" title="'.$tag_enc.'('.$v.')">'.$tag_enc.'</a> </li>';
						else
							$output.="<a href='".$script_path.($use_alt_urls?'/'.$action.'/':$action.'=').urlencode($k).($use_alt_urls?'/':"")."' style='font-size:+".(($size/100)*22)."pt'>".stripslashes($k)."</a>";
					}
				}
		}
		if(!empty($output)&&!$use_flash)
			$output='<div class="tcloud_container">
			<ul class="tcloud'.($alpha_cols==0&&$px?' tcloud_px':'').'">'.$output.'</ul>
			</div><div style="clear:left"></div>';
		return $output;
	}

	public static function dateTimeInput($id,$date,$time_format,$month_names_ar,$xtrajs='',$iid=true)
	{
		$tf=intval($time_format);
		$f_min_sec=array();
		for($n=0; $n<60; $n++) $f_min_sec[]=($n<10)?'0'.strval($n):strval($n);

		if($tf==12)
		{
			$hours_array=array('0','1','2','3','4','5','6','7','8','9','10','11','12');
			$ampm_array=array('AM','PM');
			$ampm=date('A',$date);
		}
		else
			$hours_array=array('0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23');

		$dateValue=Date::dp($month_names_ar,$date);
		$hour=date(($tf==12?'g':'G'),$date);
		$min=date('i',$date);

		$cd='<input class="input1 '.$id.'" '.($iid?'id="'.$id.'"':'').' name="'.$id.'" type="text" readonly="readonly" value="'.$dateValue.'"'.$xtrajs.'>'
			.'@'.self::buildSelect($id.'_hour',$hours_array,$hour,'','value').'<span class="rvts8">:</span>'
			.self::buildSelect($id.'_min',$f_min_sec,$min,'','value');

		if($tf==12)
			$cd.=self::buildSelect($id.'_ampm',$ampm_array,$ampm,'','value');

		return $cd;
	}

	public static function buildCalendar($mon,$year,$first_day_ofweek,$events_by_day,$url,$month_names,$day_names,$utf_fl=false,$suf='?')
	{
  	$days_in_curr_mon=Date::daysInMonth($mon,$year);
		$month=$month_names[$mon-1];
// 'First day of week' check
		if($first_day_ofweek==1)
			$firstday=date('w',mktime(0,0,0,$mon,1,$year));
		else
		{
			$day=date('w',mktime(0,0,0,$mon,1,$year));
			$firstday=($day==0?6:$day-1);
			$temp=$day_names[0];
			$day_names_rev=$day_names;
			array_shift($day_names_rev);
			array_push($day_names_rev,$temp);
		}
		settype($firstday,'integer');
		$cal_pointer=$firstday;
		$row_counter=0;

		$nav_prev=Navigation::cal($mon,$year,'prev',$url.$suf);
		$nav_next=Navigation::cal($mon,$year,'next',$url.$suf);

		$html='<table cellpadding="0" cellspacing="0"><tr><td><div class="cal_bg">
			<table class="calendar" cellspacing="0">
			<tr><td colspan="8" class="calh1"><div style="position:relative;height:16px;width:100%;">';

		// internal <>
		$html.='<div style="width:100%;text-align:center;">'.Formatter::mySubstr($month,0,3,$utf_fl).' '.$year.'</div>
			<div style="position:absolute;top:0px;left:0;">'.$nav_prev.'</div>
			<div style="position:absolute;top:0px;right:0px">'.$nav_next.'</div>
			</div></td></tr><tr>';

//weekday names
		foreach(($first_day_ofweek==1?$day_names:$day_names_rev) as $v)
			$html.='<td class="calh2">'.Formatter::mySubstr($v,0,1,$utf_fl).'</td>';

		$html.='</tr><tr>';
//last days from previous month
		if($firstday!=0||($mon==2&&$days_in_curr_mon==28))
		{
			$days_prev_mon=($mon==1)?Date::daysInMonth(12,$year):Date::daysInMonth(($mon-1),$year);
			if($firstday!=0)
			{
				$t=$days_prev_mon-$firstday+1;
				for($i=0; $i<$firstday; $i++)
				{
					$html.='<td class="day3m">'.$t.'</td>';
					$t++;
				}
			}
			else
			{
				$t=$days_prev_mon-6;
				for($i=0; $i<7; $i++)
				{
					$html.='<td class="day3m">'.$t.'</td>';
					$t++;
				} $html.='</tr>';
			}
		}
//  displaying days from selected month
		for($i=1; $i<=$days_in_curr_mon; $i++)
		{
			if($cal_pointer>6)
			{
				$cal_pointer=0;
				$html.='</tr><tr>';
				$row_counter++;
			}
			if(array_key_exists(($i),$events_by_day))
			{
				$html.='<td class="'.(Date::isCurrentDay($i,$mon,$year)?'currday':'day2m').'">
					<a style="position:relative;z-index:1;" class="'.(Date::isCurrentDay($i,$mon,$year)?'currday':'calurl').'" href="'.$url.$suf.'mon='.$mon.'&amp;year='.$year.'&amp;day='.$i.'">'.$i.'</a>
					</td>';
			}
			else
				$html.='<td class="'.(Date::isCurrentDay($i,$mon,$year)?'currday':'day1m').'">'.$i.'</td>';
			$cal_pointer++;
		}
//  displaying first days from next month
		$next_month_days=1;
		while($cal_pointer<=6)
		{
			$html.='<td class="day3m">'.$next_month_days.'</td>';
			$next_month_days++;
			$cal_pointer++;
		}
		$html.='</tr>';
		$row_counter++;
		if($row_counter<6)
		{
			$html.="<tr>";
			$cal_pointer=0;
			while($cal_pointer<=6)
			{
				$html.='<td class="day3m">'.$next_month_days.'</td>';
				$next_month_days++;
				$cal_pointer++;
			}
			$html.='</tr>';
		}
		$html.='</table></div></td></tr></table>';
		return $html;
	}

	public static function ogMeta($page_src,$tags,$fb_api_id='')
	{
		$macro=strpos($page_src,'<!--rss_meta-->')!==false?'<!--rss_meta-->':'<!--scripts-->';
		$meta='';
		//if(self::$f->xhtml_on)
		{
			if($fb_api_id!='')
				$meta.='<meta property="fb:app_id" content="'.$fb_api_id.'">'.F_LF;
			foreach($tags as $k=> $v)
				$meta.='<meta property="og:'.$k.'" content="'.$v.'">'.F_LF;
			if($meta!='')
				$page_src=str_replace($macro,$macro.F_LF.$meta,$page_src);
		}

		$html=Formatter::GFSAbi($page_src,'<html','>');
		if(strpos($html,'xmlns:og')==false)
			$page_src=str_replace($html,str_replace('>',' xmlns:og="http://opengraphprotocol.org/schema/">',$html),$page_src);

		return $page_src;
	}

}

class MailHandler extends FuncHolder
{

	public static function resolveMail($m,$def='')
	{
		$ma=array();
		$name=$def;
		if((strpos($m,'<')!==false))
		{
			$address=Formatter::GFS($m,'<','>');
			$name=stripslashes(Formatter::GFS($m,'"','"'));
		}
		else
			$address=$m;
		$ma[]=$address;
		$ma[]=$name;
		return $ma;
	}

	public static function sendMail($to,$from,$content_html,$content_text,$subject,$page_charset,
			  $att_content='',$att_file='',$att_filetype='',$send_to_author='',$author_data=array(),
			  $send_to_bcc='',$reply_to='')
	{
		include_once('mail5.php');

		$sendto=(is_array($to))?implode(";",$to):$to;
		if($subject=='')
			$subject='Auro-reply from '.Detector::getRemoteHost();
		$result=m_sendMail($sendto,$from,stripslashes($content_html),stripslashes($content_text),stripslashes($subject),
				  $page_charset,$att_content,$att_file,$att_filetype,$send_to_author,$author_data,$send_to_bcc,$reply_to,
				  self::$f->mail_type,self::$f->return_path,self::$f->sendmail_from,self::$f->use_linefeed,self::$f->SMTP_HOST,self::$f->SMTP_PORT,self::$f->SMTP_HELLO,self::$f->SMTP_AUTH,self::$f->SMTP_AUTH_USR,self::$f->SMTP_AUTH_PWD,self::$f->admin_nickname,self::$f->SMTP_SECURE);
		return $result;
	}

	public static function sendMailCA($content_html,$subject,$send_to='',$bcc='')
	{
		$res=false;

		$settings=Formatter::GFS(self::$f->ca_settings,'<registration>','</registration>');
		$admin_email=(strpos($settings,'<admin_email>')!==false)?Formatter::GFS($settings,'<admin_email>','</admin_email>'):'';

		$from=(self::$f->sendmail_from=='')?$admin_email:self::$f->sendmail_from;
		if($from=='')
			$from='admin@'.Detector::getRemoteHost();
		$to=array(($send_to=='')?$admin_email:$send_to);

		if($to=='')
			return '<div align="left"><h1>Admin e-mail address not defined!</h1><h2>To solve the problem, go to Online Administration >> Registration Settings and define Admin Email!</h2>';

		if(in_array('UTF-8',self::$f->site_charsets_a))
			$page_charset='UTF-8';
		else
			$page_charset=(isset($_GET['charset'])?Formatter::stripTags($_GET['charset']):self::$f->site_charsets_a[0]);
		if($bcc!='')
				$res=self::sendMail($to,$from,$content_html,'',$subject,$page_charset,'','','','',array(),$bcc);
		else
				$res=self::sendMail($to,$from,$content_html,'',$subject,$page_charset);
		return $res;
	}

	public static function mailer($settings,$flag)
	{
		include_once('mailer.php');
		switch($flag)
		{
			case 'BL':$mailer=new BlogMailer($settings);
				break;
			case 'CA':$mailer=new CAMailer($settings);
				break;
			default: break;
		}
		$mailer->process();
		return $mailer->output();
	}

}

class ImportHandler extends FuncHolder
{

	public static function import($settings,$flag)
	{
		include_once('importer.php');
		switch($flag)
		{
			case 'LI':$importer=new ShopImporter($settings);
				break;
			case 'NL':$importer=new NewsImporter($settings);
				break;
			case 'CA':$importer=new CAImporter($settings);
				break;

			default: break;
		}
		$importer->process();
		return $importer->output();
	}

}

class Filter extends FuncHolder
{

	public static function adminBar($fast_nav,$left_content,$right_content)
	{
		$fast_nav_items=$fast_nav[0];
		$fast_nav_selected=$fast_nav[1];
		$output='';
		foreach($fast_nav_items as $v)
		{
			$class=(((!isset($v['status'])&&$fast_nav_selected=='')||(isset($v['status'])&&$v['status']==$fast_nav_selected))?' class="selected"':'');
			$output.='<a'.$class.' href="'.$v['url'].'">'.$v['label'].' ('.$v['count'].')'.'</a>';
		}
		$output='<div class="filter_bar">'.$output.'</div><div class="filter_bar2">'.$left_content.'<div class="filter_bar_search">'.$right_content.'</div></div>';
		return $output;
	}

	public static function build($id,$filter,$action,$style='')
	{
		return '<span style="float:right"><input title="filter" type="text" id="'.$id.'" class="input1 direct_edit" value="'.$filter.'" style="font-size:11px;'.$style.'"><input class="ui_shandle_ic4" style="display:none" type="button" onclick="'.$action.'" value=""></span>';
	}


	public static function multiUnique($array)
	{
		$new=array();
		$new1=array();

		foreach($array as $k=> $na)
			$new[$k]=serialize($na);
		$uniq=array_unique($new);
		foreach($uniq as $k=> $ser)
			$new1[$k]=unserialize($ser);
		return $new1;
	}

	public static function orderBy($defOrder,$defAsc)
	{
		$orderby=(isset($_REQUEST['orderby']))?Formatter::stripTags($_REQUEST['orderby']):$defOrder;
		$asc=(isset($_REQUEST['asc']))?Formatter::stripTags($_REQUEST['asc']):$defAsc;
		return array($orderby,$asc);
	}

	public static function imgAltTag($html)
	{
		$html=preg_replace('/<img[^>]*alt="([^"]*)"[^>]*>/i',"$1",$html);
		return $html;
	}
}

class Navigation extends FuncHolder
{

	public static function addEntry($caption,$url,$active,$id,$span='',$class='')
	{
		return array('caption'=>$caption,'url'=>$url,'id'=>$id,'active'=>$active,'span'=>$span,'class'=>$class);
	}

	public static function admin2($data,$caption='',$page_view=false)
	{
		$sel='';
		$sel_id='';
		$output=str_replace('a_navt','a_nav',self::$f->navtop).'<!--start_ca_header-->';
		foreach($data as $v)
		{
			if($v['url']=='')
				$output.=' <span>'.$v['caption'].'</span> ::';
			else
			{
				if($v['active'])
				{
					$sel=$page_view?'':$v['caption'];
					$sel_id=$v['id'];
				}
				$output.='<span class="a_nav_l'.($v['class']!=''?' '.$v['class']:'').($v['active']?' active':'').'">
					<a class="nav_link'.($v['active']?' selected ':'').'" href="'.$v['url'].'">'.$v['caption'].'</a>'
					.($v['span']!=''?' <span class="nav_logout">[<span class="ca_user">'.$v['span'].'</span>]</span>':'')
					.'<a title="'.$v['caption'].'" href="'.$v['url'].'" class="ca_nav_icon icon_'.$v['id'].'"></a></span>
			<span class="a_nav_s'.($v['active']?' active':'').'"> ::</span><span class="a_nav_r"></span>';
			}
		}
  	$output.='<span class="ca_toggle ca_nav_icon '.(CA::getCaMiniCookie()?'fa-chevron-right':'fa-chevron-left').'"></span>';
		$output.='<!--end_ca_header--></div>';

		if(!self::$f->ca_fullscreen)
			$output.='<div class="a_nav"><span id="a_caption" class="a_caption">'.$sel.$caption.'</span>'.self::$f->navend;
		else
		{
			$output.='<div class="a_nav">'.self::$f->navend;
			if($sel.$caption!='')
				$output.='<div class="a_navtitle"><span class="ca_title_icon icon_'.$sel_id.'"></span><span id="a_caption" class="a_caption">'.$sel.$caption.'</span></div>';
			$output=str_replace(array('<!--pre-nav-->','<!--post-nav-->'),array('<div class="a_header"></div>','<div class="a_footer"></div>'),$output);
		}
		return $output;
	}

	public static function user($captions,$urls,$selected='')
	{
		$output='<div class="logged_container" style="padding:2px;text-align:center;">';
		foreach($captions as $k=> $v)
		{
			$format_user='';
			$value=$v;
			if(empty($urls[$k]))
				$output.=' <span class="rvts8 logged_span">'.$value.'</span> |';
			elseif($k==$selected)
				$output.=' <a class="rvts8 logged_link" href="'.$urls[$k].'">'.$value.'</a> |';
			else
			{
				if(strpos($v,'[')!==false)
				{
					$user=Formatter::GFSAbi($v,'[',']');
					$format_user=' <span class="rvts8 logged_span">'.$user.'</span>';
					$value=str_replace($user,'',$v);
				}
				if(!empty($v)&&$v!=' ')
					$output.=' <a class="rvts12 logged_link" href="'.$urls[$k].'">'.$value.'</a>'.$format_user.' |';
			}
		}
		$output.='<!--end_ca_header--></div>';
		return $output;
	}
	
	public static function recordsPerPage()
	{
		return self::$f->max_rec_on_admin;
	}

	public static function pageCA($rec_count,$page_url,$max,$page)
	{
		return self::page($rec_count,$page_url,$max>0?$max:self::recordsPerPage(),$page,' / ','nav',self::$f->ca_nav_labels,'&amp;','',false,false,'',true);
	}

	public static function page($rec_count,$page_url,$rec_per_page,$page=1,$of_label='of',$class='rvts12',$src_labels,$pg_prefix='&amp;',$lang='',$url2_flag=false,$addhome=false,$homeurl='',$ca=false,$params='')
	{
		if($rec_per_page==0)
			return '';
		$output='';
		$purl=($url2_flag?$page_url:$page_url.$pg_prefix.'page=');
		$cl_url=($url2_flag?'/'.$pg_prefix:$lang);

		if(!isset($src_labels['home']))
			$src_labels['home']='home';
		$compact=strpos($params,'compact')!==false; //comapct labels : same as ca labels + 123  >
		$labels=$compact?self::$f->ca_nav_labels:$src_labels;

		$lb=$compact?'':'<span class="'.$class.' nav_brackets left">[</span>';
		$rb=$compact?'':'<span class="'.$class.' nav_brackets right">]</span>';
		$class=strpos($class,'class=')!=false?Formatter::GFS($class,'"','"'):$class;
		$div_class=$ca?'class="ca_nav"':'class="user_nav"';

		$labels['home_title']=$src_labels['home'];
		$labels['prev_title']=$src_labels['prev'];
		$labels['next_title']=$src_labels['next'];

		$tabsmax=6;
		if($rec_per_page<1)
			$rec_per_page=1;
		$pcount=round(($rec_count-1)/$rec_per_page)+1;
		$pcount=ceil($rec_count/$rec_per_page);

		if($rec_count>0)
		{
			$output.='<div '.$div_class.'><table style="width:100%"><tr><td><span class="rvts8">';
			if($addhome)
				$output.=$lb.'<a class="'.$class.' nav_home" href="'.$homeurl.'" title="'.$labels['home_title'].'">'.$labels['home'].'</a>'.$rb.'&nbsp;';
			if($rec_per_page>0)
			{
				if($page>1)
					$output.=$lb.'<a class="'.$class.' nav_prev" href="'.$purl.($page-1).$cl_url.'" title="'.$labels['prev_title'].'">'.$labels['prev'].'</a>'.$rb.'&nbsp;';
				if($pcount<=$tabsmax)
				{
					$start=1;
					$stop=$pcount;
				}
				else
				{
					$start=$page-round($tabsmax/2);
					$start=max($start,1);
					$stop=$start+$tabsmax;
					if($stop>$pcount)
					{
						$stop=$pcount;
						$start=$stop-$tabsmax+1;
					}
				}
				if($start>1)
				{
					$output.='<a class="'.$class.'" href="'.$purl.'1'.$cl_url.'">1</a> ';
					if($start>2)
						$output.='<span class="'.$class.' nav_dots"> ... </span>';
				}

				if($start!=$stop)
					for($i=$start; $i<$stop+1; $i++)
					{
						if($i==$page&&$page<=$pcount)
							$output.=$lb.'<span class="'.$class.' nav_active">'.$i.'</span>'.$rb;
						else
							$output.=' <a class="'.$class.'" href="'.$purl.$i.$cl_url.'">'.$i.'</a> ';
					}

				if($stop<$pcount)
				{
					if($stop<$pcount-1)
						$output.='<span class="'.$class.' nav_dots"> ... </span>';
					$output.=' <a class="'.$class.'" href="'.$purl.($pcount).$cl_url.'">'.$pcount.'</a>';
				}

				if($page<$pcount)
					$output.='&nbsp;'.$lb.'<a class="'.$class.' nav_next" href="'.$purl.($page+1).$cl_url.'" title="'.$labels['next_title'].'">'.$labels['next'].'</a>'.$rb;

				$output.='</span></td>';
				if($rec_count>1)
				{
					$output.='<td style="text-align:right"><span class="rvts8 '.$class.' nav_count">'.(($page-1)*$rec_per_page+1).'-'
						.($rec_per_page*$page>$rec_count?$rec_count:$rec_per_page*$page).' '.$of_label.' '.$rec_count.'</span></td>';
				}
				$output.='</tr></table></div>';
			}
			else
			{
				$output='<div '.$div_class.' style="text-align:right;padding: 2px 0;">';
				if($addhome)
					$output.=$lb.'<a class="'.$class.' nav_home" href="'.$homeurl.'" title="'.$labels['home_title'].'">'.Formatter::strToUpper($labels['home']).'</a>'.$rb.'&nbsp;';
				$output.='<span class="rvts8 '.$class.' nav_count">1-'.$rec_count.' '.$of_label.' '.$rec_count.'</span></div>';
			}
		}
		return $output;
	}

	public static function entry($prev,$next,$prev_title,$next_title,$page_url,$labels,$url2_flag=false,$params='')
	{
		$output='';
		$class='rvts12';
		$compact=strpos($params,'compact')!==false;
		$floating=strpos($params,'floating')!==false;

		if($compact)
			$labels=self::$f->ca_nav_labels;

		$lb=$compact?'':'<span class="'.$class.' nav_brackets left">[</span>';
		$rb=$compact?'':'<span class="'.$class.' nav_brackets right">]</span>';
		$div_class='class="user_nav"';
		if(!isset($labels['home']))
			$labels['home']='home';

		$output.='<div '.$div_class.' style="padding: 2px 0;"><table style="width:100%"><tr><td><span class="rvts8">';
		if($floating)
		{
			if($prev!='')
				$output.='<div style="float:left;text-align:left;">'.$lb.'<a class="'.$class.' nav_prev" href="'.$prev.'" title="'.Formatter::sth($prev_title).'">'.$labels['prev'].'</a>'.$rb.F_BR.Formatter::sth($prev_title).'&nbsp;</div>';
			if($next!='')
				$output.='<div style="float:right;text-align:right;">'.$lb.'<a class="'.$class.' nav_next" href="'.$next.'" title="'.Formatter::sth($next_title).'">'.$labels['next'].'</a>'.$rb.F_BR.Formatter::sth($next_title).'</div>';
			$output.='<div style="width:20%; margin: 10px auto;text-align:center;">'.$lb.'<a class="'.$class.' nav_home" href="'.$page_url.'" title="'.$labels['home'].'">'.$labels['home'].'</a>'.$rb.'&nbsp;</div>';
			$output.='<div style="clear:both;"></div></span></td></tr></table></div>';
		}
		else
		{
			$output.=$lb.'<a class="'.$class.' nav_home" href="'.$page_url.'" title="'.$labels['home'].'">'.$labels['home'].'</a>'.$rb.'&nbsp;';
			if($prev!='')
				$output.=$lb.'<a class="'.$class.' nav_prev" href="'.$prev.'" title="'.Formatter::sth($prev_title).'">'.$labels['prev'].'</a>'.$rb.'&nbsp;';
			if($next!='')
				$output.=$lb.'<a class="'.$class.' nav_next" href="'.$next.'" title="'.Formatter::sth($next_title).'">'.$labels['next'].'</a>'.$rb;
			$output.='</span></td></tr></table></div>';
		}
		return $output;
	}

	public static function cal($mon,$year,$type,$url)  // calendar < > navigation
	{
		$output='';
		$prev_mon=$mon-1;
		$prev_year=$year;
		$next_mon=$mon+1;
		$next_year=$year;

		if($mon==1&&$year>1950)
		{
			$prev_mon=12;
			$prev_year=$year-1;
		}
		elseif($mon==1&&$year<=1950)
		{
			$prev_mon=1;
			$prev_year=1950;
		}
		elseif($mon==12&&$year<2050)
		{
			$next_mon=1;
			$next_year=$year+1;
		}
		elseif($mon==12&&$year>=2050)
		{
			$next_mon=12;
			$next_year=2050;
		}

		$output.='<span style="background:transparent;width:12px;cursor:pointer;" onclick="document.location=\''.$url;
		if($type=='prev')
			$output.="mon=".$prev_mon."&amp;year=".$prev_year;
		else
			$output.="mon=".$next_mon."&amp;year=".$next_year;
		$output.='\';">'.($type=='prev'?'&lt;':'&gt;').'</span>';
		return $output;
	}
}

class Linker extends FuncHolder
{

	public static function getHost()
	{
		$host='';
		if(self::$f->use_hostname&&isset($_SERVER['HTTP_HOST']))
			$host=$_SERVER['HTTP_HOST'];
		elseif(isset($_SERVER['SERVER_NAME']))
			$host=$_SERVER['SERVER_NAME'];
		if($host=='')
			return $host; //host not found, return empty and get out
		if(isset($_SERVER['SERVER_PORT'])&&$_SERVER['SERVER_PORT']!="80"&&$_SERVER['SERVER_PORT']!="443")
			$host .= ':'.$_SERVER['SERVER_PORT'];

		return $host;
	}

	public static function requestUri()
	{
		if(isset($_SERVER['REQUEST_URI']))
			$uri=$_SERVER['REQUEST_URI'];
		else
		{
			if(isset($_SERVER['argv']))
				$uri=$_SERVER['SCRIPT_NAME'].(isset($_SERVER['argv'][0])?'?'.$_SERVER['argv'][0]:'');
			elseif(isset($_SERVER['QUERY_STRING']))
				$uri=$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
			else
				$uri=$_SERVER['SCRIPT_NAME'];
		}
		$uri='/'.ltrim($uri,'/');
		return $uri;
	}

	public static function buildSelfURL($script_name,$use_alt_urls=false)
	{
		if(isset($_SERVER['SCRIPT_URI'])&&$use_alt_urls==false&&
			//these additional checks added due to wrong SCRIPT_URI when rewrite rule used (on some servers only)
			(strpos($_SERVER['SCRIPT_URI'],'.html')!==false||strpos($_SERVER['SCRIPT_URI'],'.php')!==false)
		)
			return $_SERVER['SCRIPT_URI'];
		else if((isset($_SERVER['SCRIPT_NAME']))&&(strpos($_SERVER['SCRIPT_NAME'],$script_name)!==false))
			return self::$f->http_prefix.self::getHost().$_SERVER['SCRIPT_NAME'];
		else
			return self::$f->http_prefix.self::getHost().dirname($_SERVER['PHP_SELF']).(dirname($_SERVER['PHP_SELF'])=='/'?'':'/').$script_name;
	}

	public static function redirect($url,$temp_redirect_on=false)
	{
		if(self::$f->httpRedirect)
			echo '<meta http-equiv="refresh" content="0;url='.$url.'">';
		else
		{
			if($temp_redirect_on)
				header("HTTP/1.0 307 Temporary redirect"); header('Location:'.str_replace('&amp;','&',$url));
		}
	}

	public static function buildReturnURL($has_param=true)
	{
		$r=base64_encode(self::currentPageUrl());
		if($has_param)
			$r='&amp;r='.$r;
		return $r;
	}

//redirects to given path or returns false if no such path is provided
	public static function checkReturnURL($check_only=false,$get_clean=false)
	{
		if(isset($_REQUEST['r'])&&$_REQUEST['r']!='')
		{
			$r=$_REQUEST['r'];
			if($check_only&&!$get_clean)
				return $r;  //check only and there is something to return to
			$r=base64_decode($r);
			//don't return to duplicate if coming from there
			$r=preg_replace('/action=duplicate&entry_id=(\d+)$/','action=index',$r);
			if($check_only&&$get_clean)
				return $r;  //checks and gets pure returning url
			self::redirect($r);
			exit;
		}
		return false;
	}

	public static function relPathBetweenURLs($path_1,$path_2)
	{
		//calculate rel path from symlinks folder to file dest folder
		if(strpos($path_1,'innovaeditor')!==false)
		{
			$common=Formatter::longestCommonSubsequence($path_1,$path_2);
			$path_1_part=str_replace($common,'',$path_1);
			$path_2_part=str_replace($common,'',$path_2);
		}
		else
		{
			$path_1_part=str_replace('../','',$path_1); //assuming innovaeditor is always in root
			$path_2_part=$path_2;
		}
		$path_2_part_dirs=substr_count($path_2_part,'/');
		$pref_path='';
		for($i=$path_2_part_dirs; $i>0; $i--)
			$pref_path .= '../';
		return $pref_path.$path_1_part;
	}

	public static function url()
	{
		if(isset($_SERVER['SCRIPT_URI']))
			return $_SERVER['SCRIPT_URI'];
		elseif(isset($_SERVER['SCRIPT_NAME']))
			return self::getHost().$_SERVER['SCRIPT_NAME'];
		else
			return self::getHost().$_SERVER['PHP_SELF'];
	}

	public static function cleanURL($url,$lower=true)
	{
		$url=preg_replace("`\[.*\]`U","",$url);
		$url=preg_replace('`&(amp;)?#?[a-z0-9_]+;`i','-',$url);
		$url=htmlentities($url,ENT_COMPAT,'utf-8');
		$url=preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1",$url);
		$url=preg_replace(array("`[^a-z0-9_]`i","`[-]+`"),"-",$url);
		if($lower)
			return Formatter::strToLower(trim($url,'-'));
		else
			return trim($url,'-');
	}

	public static function currentPageUrl()
	{

		$pageURL='http';
		$request_URI=self::requestUri();
		if(isset($_SERVER["HTTPS"])&&$_SERVER["HTTPS"]=="on")
			$pageURL.="s";
		$pageURL.="://";
		$pageURL.=self::getHost().$request_URI;

		return $pageURL;
	}

	public static function removeURLMultiSlash($url)
	{
		return preg_replace('%([^:])([/]{2,})%','\\1/',$url);
	}

}

class File extends FuncHolder
{

	//read/write db files functions
	public static function read($filename)
	{
		$contents='';
		clearstatcache();
		if(file_exists($filename))
		{
			$fsize=filesize($filename);
			if($fsize>0)
			{
				$fp=fopen($filename,'r');
				$contents=fread($fp,$fsize);
				fclose($fp);
			}
		}
		if(version_compare(PHP_VERSION,'5.4.0','<'))
			if(get_magic_quotes_runtime())
				$contents=stripslashes($contents);
		return $contents;
	}

	public static function readTaggedData($file,$tag)
	{
		$file_contents=self::read($file);
		if($file_contents==''&&strpos($file,'../')!==false)
			$file_contents=self::read(str_replace('../','',$file));
		$setting=Formatter::GFS($file_contents,'<'.$tag.'>','</'.$tag.'>');
		return $setting;
	}

	public static function writeTaggedData($tags,$newset,$db_settings_file,$template_fname,$del_flag=false)
	{
		$file_contents='<?php echo "hi"; exit; /* */ ?>';
		clearstatcache();
		if(!file_exists($db_settings_file))
		{
			print Formatter::fmtInTemplate($template_fname,Formatter::fmtErrorMsg('MISSING_DBFILE',$db_settings_file));
			exit;
		}
		elseif(!$fp=fopen($db_settings_file,'r+'))
		{
			print Formatter::fmtInTemplate($template_fname,Formatter::fmtErrorMsg('DBFILE_NEEDCHMOD',$db_settings_file));
			exit;
		}
		else
		{
			flock($fp,LOCK_EX);
			$fsize=filesize($db_settings_file);
			if($fsize>0)
				$file_contents=fread($fp,$fsize);

			if(!is_array($tags))
			{
				$tags_arr=array($tags);
				$newset_arr=array($newset);
			}
			else
			{
				$tags_arr=$tags;
				$newset_arr=$newset;
			}

			foreach($tags_arr as $k=> $type)
			{
				if(strpos($file_contents,"<$type>")!==false)
				{
					$oldsettings=Formatter::GFS($file_contents,"<$type>","</$type>");
					$file_contents=str_replace("<$type>".$oldsettings."</$type>",($del_flag==true?'':"<$type>".$newset_arr[$k]."</$type>"),$file_contents);
				}
				else
					$file_contents=str_replace("*/ ?>","<$type>".$newset_arr[$k]."</$type>*/ ?>",$file_contents);
			}
			ftruncate($fp,0);
			fseek($fp,0);
			if(fwrite($fp,$file_contents)===FALSE)
			{
				print "Cannot write to file";
				exit;
			}
			flock($fp,LOCK_UN);
			fclose($fp);
			return true;
		}
	}

	public static function readLangSet($file,$lang,$page_type,$period_list=array())
	{

		$result=array();
		if(file_exists($file))
		{
			$content=file_get_contents($file);
			if($content!==false)
			{
				$en=Formatter::GFS($content,'[EN]','[END]');
				$ln=Formatter::GFS($content,'['.$lang.']','[END]');
				$content='';

				$lines_en=explode("\n",$en);
				$count_en=count($lines_en);
				for($i=1; $i<$count_en; $i++)
				{
					$label=explode("=",trim($lines_en[$i]));
					if(!empty($label[0]))
						$default_lang_l["{$label[0]}"]=trim($label[1]);
				}

				$lines_ln=explode("\n",$ln);
				$count_ln=count($lines_ln);
				for($i=1; $i<$count_ln; $i++)
				{
					$label=explode("=",trim($lines_ln[$i]));
					if(in_array($page_type,array('blog','podcast','photoblog','calendar','guestbook')))
					{
						if(in_array($label[0],self::$f->day_names))
							$new_day_name[]=trim($label[1]);
						elseif(in_array($label[0],self::$f->month_names))
							$new_month_name[]=trim($label[1]);
						if($page_type=='calendar')
						{
							if(in_array($label[0],$period_list))
								$new_period_list[]=trim($label[1]);
							elseif(in_array($label[0],array('year','month','week')))
								$new_repeatPeriod_list[]=trim($label[1]);
						}
					}
					if(!empty($label[0]))
						$new_lang_l["{$label[0]}"]=trim(isset($label[1])?$label[1]:$label[0]);
				}
			}

			if(isset($new_lang_l))
			{
				foreach($default_lang_l as $k=> $v)
				{
					if(isset($new_lang_l[$k]))
						$default_lang_l[$k]=$new_lang_l[$k];
				}
				$result['lang_l']=$default_lang_l;
			}
			else
			{
				$result['lang_l']=$default_lang_l;
			}

			if(isset($new_day_name))
				$result['day_name']=$new_day_name;
			if(isset($new_month_name))
				$result['month_name']=$new_month_name;
			if(isset($new_period_list))
				$result['period_list']=$new_period_list;
			if(isset($new_repeatPeriod_list))
				$result['repeatPeriod_list']=$new_repeatPeriod_list;
		}
		return $result;
	}

}

class PageHandler extends FuncHolder
{

	//search reindex
	public static function getContent($fname,$include_earea=false)
	{
		if(!file_exists($fname))
			return ''; //no file found, no page content
		$content=File::read($fname);
		$content=self::getArea($content,$include_earea);
		return $content;
	}

	public static function getArea($content,$include_earea=false,$exclude_body_tag=false)
	{
		if(strpos($content,'<!--page-->')!==false)
		{
			$earea_buff='';
			if($include_earea)
			{
				while(strpos($content,'<!--%areap')!==false)
				{
					$earea_st=Formatter::GFSAbi($content,'<!--%areap','%-->');
					$earea=Formatter::GFS($content,$earea_st,'<!--areaend-->');
					$earea_buff.=$earea.' ';
					$content=str_replace($earea_st.$earea.'<!--areaend-->','',$content);
				}
			}
			$content=Formatter::GFS($content,'<!--page-->','<!--/page-->');
			$content=$earea_buff.$content;
		}
		else
		{
			$content=str_replace(array('<BODY','</BODY'),array('<body','</body'),$content);
			$pattern=Formatter::GFSAbi($content,'<body','</body>');
			$body_start_tag=substr($pattern,0,strpos($pattern,'>')+1);
			if($exclude_body_tag)
				$content=Formatter::GFS($content,$body_start_tag,'</body>');
			else
				$content=Formatter::GFSAbi($content,$body_start_tag,'</body>');
		}
		if(!$include_earea)
		{
			while(strpos($content,'<!--%areap')!==false)
				$content=str_replace(Formatter::GFSAbi($content,'<!--%areap','<!--areaend-->'),'',$content);
		}
		return $content;
	}

}

class Editor extends FuncHolder
{

	public static function getEditor($lang,$rel,$rtl,$ed_bg,&$html,&$js,$use_mini=false)
	{

		$langl=isset(self::$f->innova_lang_list[$lang])?self::$f->innova_lang_list[$lang]:self::$f->innova_lang_list['english'];

		$html=str_replace(array('%RELPATH%','%BACKGROUND%','%XLANGUAGE%'),array($rel,$ed_bg,$langl),self::$f->editor_html);
		$js=str_replace(array('%EDITOR_LANGUAGE%','%RELPATH%','%XLANGUAGE%'),array($lang,$rel,$langl),self::$f->editor_js);

		if(self::$f->tiny&&$lang=='en')
			$html=str_replace("plugins :","gecko_spellcheck : true,plugins :",$html);
		if(self::$f->tiny)
			$html=str_replace('language : "en",','language : "'.$lang.'",',$html);
		else
			$html=str_replace('%EDITOR_LANGUAGE%',$lang,$html);

		$rtl_code='';
		if($rtl)
			$rtl_code=self::$f->tiny?'directionality:"rtl",':'oEdit1.btnLTR=true;oEdit1.btnRTL=true;';
		$html=str_replace('%RTL%',$rtl_code,$html);
		if($use_mini)
		{
			if(self::$f->tiny)
			{

			}
			else
			{
				$grps_def=Formatter::GFSAbi($html,'oEdit1.groups= [','];');
				$file_brows=Formatter::GFSAbi($html,'oEdit1.fileBrowser="','";');
				$html=str_replace($grps_def,'oEdit1.groups= [["group1","",["Bold","Italic", "Underline",'
					.'"ForeColor","BackColor","FontName","FontSize","JustifyLeft","JustifyCenter","JustifyRight","Emoticons","Line","LinkDialog"]]];',$html);
				$html=str_replace($file_brows,'',$html);
				$html=str_replace('</script>','oEdit1.enableLightbox=false;oEdit1.enableCssButtons=false;oEdit1.enableFlickr= false;</script>',$html);
			}
		}
		$js=str_replace('%RTL%',$rtl_code,$js);
	}

	public static function updateLang($pl,$rel_path,&$innova_js,&$innova_def)
	{

		$l=strtolower(self::$f->names_lang_sets[$pl]);
		if(in_array($l,self::$f->innova_lang_list))
		{
			$la=(strpos($innova_js,'istoolbar.js')!==false)?$l:self::$f->innova_lang_list[$l];
			$innova_js=str_replace(Formatter::GFSAbi($innova_js,'src="'.$rel_path.'innovaeditor/scripts/language/','/editor_lang.js"'),'src="'.$rel_path.'innovaeditor/scripts/language/'.$la.'/editor_lang.js"',$innova_js);
			$innova_def=str_replace(Formatter::GFSAbi($innova_def,'assetmanager.php?lang=','&root'),'assetmanager.php?lang='.$la.'&root',$innova_def);
		}
	}

	public static function replaceClassesEdit($src)
	{
		for($i=0; $i<count(self::$f->ext_styles); $i++)
			$src=str_replace('class="rvts'.(($i+1)*8).'"','class="'.self::$f->ext_styles[$i].'"',$src);
		return $src;
	}

	public static function replaceClasses($src)
	{
		if(get_magic_quotes_gpc())
		{
			$src=str_replace(array("\'","&#92;'"),"'",$src);
			$src=str_replace(array('\"','&#92;"'),'"',$src);
			if(!self::$f->uni)
				$src=str_replace(array('&#8217;','&#8216;','&#8221;','&#8220;','Â´','`','â€?','â€œ','â€™'),array('&rsquo;','&lsquo;','&rdquo;','&ldquo;','&rsquo;','&lsquo;','&rdquo;','&ldquo;',"'"),$src);
		}

		for($i=0; $i<count(self::$f->ext_styles); $i++)
		{
			$src=str_replace('class="'.self::$f->ext_styles[$i].'"','class="rvts'.(($i+1)*8).'"',$src);
			$src=str_replace('class='.self::$f->ext_styles[$i].'>','class="rvts'.(($i+1)*8).'">',$src);
		}
		return $src;
	}
	public static function addGoogleFontsToInnova($src,$js_innova)
	{
		if(self::$f->editor=="LIVE")
		{
			$js=array();
			$fonts=join("|",self::$f->gfonts);
			$matches=array();
			if(preg_match_all('/'.$fonts.'/',$src,$matches))
			{
				$matches=array_unique($matches[0]);
				foreach($matches as $v)
					$js[]='"'.$v.'"';
			}
			if(count($js)>0)
			{
				$js_innova.='
		<script type="text/javascript">
		if(typeof oEditFonts==="undefined") var oEditFonts=new Array();';
				foreach($js as $v)
					$js_innova.='oEditFonts.push('.$v.');';
				$js_innova.='</script>';
			}
		}
		return $js_innova;
	}

	public static function fixInnovaPaths($content,$script_name,$full_script_path,$rel_path)
	{
		$full_script_path2=str_replace("/".$script_name,'',$full_script_path);
		$abs_url=($rel_path==''?$full_script_path2:substr($full_script_path2,0,strrpos($full_script_path2,'/'))).'/innovaeditor/assets/';
		$content=str_replace('="../innovaeditor/assets/','="innovaeditor/assets/',$content);
		$content=str_replace('src="innovaeditor/assets/','src="'.$abs_url,$content);
		$content=str_replace('href="innovaeditor/assets/','href="'.$abs_url,$content);
		return $content;
	}

	//these (two) functions were in the innova files,extracted as single function as used more than once.
//logged user and admin paramethers are by reference, because they are declared before and used
//after the functions use in the script
	public static function innovaAuth(&$logged_user,&$logged_admin,&$is_adminUser,$check_innova_access_only=false)
	{
		global $user;
		Session::intStart();
		$err='';
		$oep_pass_mode=(Session::isSessionSet('page_id')&&Session::isSessionSet('cur_pwd'.Session::getVar('page_id'))?true:false);
		$pass_mode=(Session::isSessionSet('page_id')&&Session::isSessionSet('admin'.Session::getVar('page_id'))?true:false);
		if(!Cookie::isAdmin())
		{
			if(!$user->userCookie())
			{
				if(!$pass_mode&&!$oep_pass_mode)
					$err=self::innovaHandleError($check_innova_access_only);
			}
			elseif(self::innovaCheckAuth($user->getUserCookie())==false)
				$err=self::innovaHandleError($check_innova_access_only);
			else
				$logged_user=$user->getUserCookie();
		}
		else
			$logged_admin='admin';
		if($err!='')
			return $err;
	}

	public static function innovaHandleError($check_innova_access_only=false)
	{
		if($check_innova_access_only)
			return 'forbidden';
		else
		{
			echo "Not allowed!";
			exit;
		}
	}

	public static function innovaCheckAuth($username,$user_account=null)
	{
		global $user;
		$auth=false;
		if($user_account==null)
			$user_account=$user->getUser($username,'../../');
		if(!empty($user_account))
		{
			if($user_account['access'][0]['section']!='ALL')
			{
				foreach($user_account['access'] as $v)
				{
					if($v['type']=='1')
					{
						$auth=true;
						break;
					}
					elseif($v['type']=='2'&&isset($v['page_access']))
					{
						foreach($v['page_access'] as $val)
						{
							if($val['type']=='1'||$val['type']=='3')
							{
								$auth=true;
								break;
							}
						}
					}
				}
			}
			else
			{
				if(isset($user_account['access'][0]['type'])&&$user_account['access'][0]['type']=='1')
					$auth=true;
			}
		}
		return $auth;
	}

}

//used when data is generated in a file or on the display
class output_generator extends FuncHolder
{
	public static function printEntry($btn_id,$rel_path,$output,$template,$use_page_bg=true,$css='')
	{
		$print_html='<a id="'.$btn_id.'" href="javascript:void(0);" style="padding:2px;">'.Builder::printImgHtml($rel_path).'</a>'
			.($use_page_bg?'<div id="xm1" style="float:none;width:970px;"><div id="xm2">'.$output.'</div></div>':$output);
		$print_js='<script type="text/javascript">
		$(document).ready(function(){$("link[media=\'print\']").remove();$("#'.$btn_id.'").click(function(){$(this).hide();window.print();$(this).show();});});
</script>
';

		$body_part=Formatter::GFSAbi($template,'<body','</body>');
		$template=str_replace($body_part,'<body class="print_preview" style="background:transparent">'.$print_html.'</body>',$template);
		$template=str_replace('<!--endscripts-->',$css.$print_js.'<!--endscripts-->',$template);
		$template=str_replace(array('<script type="text/javascript" src="documents/script.js"></script>',
			'<script type="text/javascript" src="../documents/script.js"></script>'),'',$template);
		print $template;
		exit;
	}

	public static function sendFileHeaders($fname,$c_type='application/octet-stream')
	{
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: $c_type");
		header("Content-Disposition: attachment; filename=\"".$fname."\";");
		header("Content-Transfer-Encoding: binary");
	}

	public static function downloadFile($path,$new_filename='')
	{
		define('F_STREAM_BUFFER',4096);
		define('F_STREAM_TIMEOUT',86400);
		define('F_USE_OB',false);

		$AllowedTypes = "|gif|jpg|jpeg|png|mp3|mp4|swf|asf|avi|mpg|mpeg|wav|wma|mid|wmw|mov|ram|bmp|pdf|zip|rar|xml|doc|docx|flv|xls|xlsx|ppt|dwg|gpx|";

		$ext=pathinfo($path,PATHINFO_EXTENSION);
		if((strpos($AllowedTypes,'|'.$ext.'|')===false) || (substr_count(str_replace('..','',$path),'.')!=1))
			die('ERROR: Illegal file format');

		$filesize=filesize($path);
		$filename=basename($path);
		if(empty($new_filename))
			$new_filename=$filename;

		$file=@fopen($path,'r') or die("can't open file");
		$sm=ini_get('safe_mode');
		if(!$sm&&function_exists('set_time_limit')&&strpos(ini_get('disable_functions'),'set_time_limit')===false)
			set_time_limit(F_STREAM_TIMEOUT);

		$partialContent=false;
		if(isset($_SERVER['HTTP_RANGE']))
		{
			$rangeHeader=explode('-',substr($_SERVER['HTTP_RANGE'],strlen('bytes=')));
			if($rangeHeader[0]>0)
			{
				$posStart=intval($rangeHeader[0]);
				$partialContent=true;
			}
			else
				$posStart=0;
			if($rangeHeader[1]>0)
			{
				$posEnd=intval($rangeHeader[1]);
				$partialContent=true;
			}
			else
				$posEnd=$filesize-1;
		}
		else
		{
			$posStart=0;
			$posEnd=$filesize-1;
		}
		/*		 * **** HEADERS ***** */
		$ext=end(explode(".",strtolower($new_filename)));
		$mime=Detector::getMime($ext);
		header("Content-type: ".$mime);
		header('Content-Disposition: attachment; filename="'.$new_filename.'"');
		header("Content-Length: ".($posEnd-$posStart+1));
		header('Date: '.gmdate('D, d M Y H:i:s \G\M\T',time()));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T',filemtime($path)));
		header('Accept-Ranges: bytes');
		header("Cache-Control: post-check=0, pre-check=0",false);
		header("Pragma: no-cache");
		header("Expires: ".gmdate("D, d M Y H:i:s \G\M\T",mktime(date("H")+2,date("i"),date("s"),date("m"),date("d"),date("Y"))));
		if($partialContent)
		{
			header("HTTP/1.0 206 Partial Content");
			header("Status: 206 Partial Content");
			header("Content-Range: bytes ".$posStart."-".$posEnd."/".$filesize);
		}
		if($sm)
			fpassthru($file);
		else
		{
			fseek($file,$posStart);
			if(F_USE_OB)
				ob_start();
			while(($posStart+F_STREAM_BUFFER<$posEnd)&&(connection_status()==0))
			{
				echo fread($file,F_STREAM_BUFFER);
				if(F_USE_OB)
					ob_flush();
				flush();
				$posStart+=F_STREAM_BUFFER;
			}
			if(connection_status()==0)
				echo fread($file,$posEnd-$posStart+1);
			if(F_USE_OB)
				ob_end_flush();
		}
		fclose($file);
	}

}

class Formatter extends FuncHolder
{

	public static function strIReplace($search,$replace,$subject)
	{
		if(function_exists('str_ireplace'))
			$subject=str_ireplace($search,$replace,$subject);
		else
		{
			$ls=$search;
			foreach($ls as $v)
				$search[]=strtoupper($v);
			$subject=str_replace($search,$replace,$subject);
		}
		return $subject;
	}

	public static function strLReplace($search,$replace,$subject) //replace last occurrence
	{
		$pos=strrpos($subject,$search);
		if($pos!==false)
			$subject=substr_replace($subject,$replace,$pos,strlen($search));
		return $subject;
	}

	public static function intVal($v)
	{
		return intval(preg_replace("/[^0-9]/","",$v));
	}

	public static function parseDropdown($temp,$i)
	{
		for($ii=1; $ii<5; $ii++)
		{
			$drop_down_id='a'.$ii;
			$temp=str_replace("ToggleBody('".$drop_down_id."'","ToggleBody('".$drop_down_id."_".$i."'",$temp);
			$temp=str_replace('id="'.$drop_down_id.'Body','id="'.$drop_down_id.'_'.$i.'Body',$temp);
			$temp=str_replace('id="'.$drop_down_id.'Up','id="'.$drop_down_id.'_'.$i.'Up',$temp);
		}
		return $temp;
	}

	public static function longestCommonSubsequence($str_1,$str_2)
	{
		$str_1_len=strlen($str_1);
		$str_2_len=strlen($str_2);
		$result="";

		if($str_1_len===0||$str_2_len===0)
			return $result;
		$longest_common_subsequence=array();

		for($i=0; $i<$str_1_len; $i++)
		{
			$longest_common_subsequence[$i]=array();
			for($j=0; $j<$str_2_len; $j++)
				$longest_common_subsequence[$i][$j]=0;
		}
		$max_size=0;
		for($i=0; $i<$str_1_len; $i++)
		{
			for($j=0; $j<$str_2_len; $j++)
			{
				if($str_1[$i]===$str_2[$j])
				{
					if($i===0||$j===0)
						$longest_common_subsequence[$i][$j]=1;
					else
						$longest_common_subsequence[$i][$j]=$longest_common_subsequence[$i-1][$j-1]+1;

					if($longest_common_subsequence[$i][$j]>$max_size)
					{
						$max_size=$longest_common_subsequence[$i][$j];
						$result="";
					}
					if($longest_common_subsequence[$i][$j]===$max_size)
						$result=substr($str_1,$i-$max_size+1,$max_size);
				}
			}
		}
		return $result;
	}

	public static function replaceIfMacro(&$src)
	{
		$src=str_replace(array('%IF<condition>','</falsevalue>%'),array('<if><condition>','</falsevalue></if>'),$src);
		$ifc='<if><condition>';
		$fval='</falsevalue></if>';
		while(strpos($src,$ifc)!==false)
		{
			$pre=self::GFS($src,$ifc,$fval);
			while(strpos($pre,$ifc)!==false)
			{
				$pre=self::GFS($pre.$fval,$ifc,$fval);
			}
			$temp=$ifc.$pre.$fval;
			$parsed=self::parseIf($temp);
			$src=str_replace($temp,$parsed,$src);
		}
	}

	public static function replaceCopyMacro(&$src)
	{
		while(strpos($src,'%COPY[')!==false)
		{
			$m=self::GFSAbi($src,'%COPY[',']%');
			$n=self::GFS($m,'%COPY[',']%');
			$d=explode(',',$n);
			if(strpos($d[0],'<')!==false) //html ignore copy
				$v=$d[0];
			else
				$v=mb_substr($d[0],$d[1]-1,isset($d[2])?$d[2]-$d[1]+1:1000000,'UTF-8');

			$src=str_replace($m,$v,$src);
		}
	}

	public static function parseIf($macro) //moved from shop as used in survey also now
	{
		$cond=self::GFS($macro,'<condition>','</condition>');
		if(strpos($cond,' <> '))
			$eq='<>';
		elseif(strpos($cond,' <= '))
			$eq='<=';
		elseif(strpos($cond,'=> '))
			$eq='=>';
		elseif(strpos($cond,'= '))
			$eq='=';
		elseif(strpos($cond,' < '))
			$eq='<';
		elseif(strpos($cond,' > '))
			$eq='>';
		elseif(strpos($cond,'<>'))
			$eq='<>';
		elseif(strpos($cond,'<='))
			$eq='<=';
		elseif(strpos($cond,'=>'))
			$eq='=>';
		elseif(strpos($cond,'='))
			$eq='=';
		elseif(strpos($cond,'<'))
			$eq='<';
		elseif(strpos($cond,'>'))
			$eq='>';
		else
			$eq='';

		$trueval=self::GFS($macro,'<truevalue>','</truevalue>');
		$falseval=self::GFS($macro,'<falsevalue>','</falsevalue>');
		$lc=trim(self::GFS($cond,'',$eq));
		$rc=trim(self::GFS($cond,$eq,''));
		$res=$falseval;
		if($eq=='=')
		{
			if($lc==$rc)
				$res=$trueval;
		}
		else if($eq=='>')
		{
			if($lc>$rc)
				$res=$trueval;
		}
		else if($eq=='<')
		{
			if($lc<$rc)
				$res=$trueval;
		}
		else if($eq=='<=')
		{
			if($lc<=$rc)
				$res=$trueval;
		}
		else if($eq=='=>')
		{
			if($lc>=$rc)
				$res=$trueval;
		}
		else if($eq=='<>')
		{
			if($lc!=$rc)
				$res=$trueval;
		}
		return $res;
	}

	public static function formatPageView($page,$apanel,$rel_path)
	{

		$body_tag=self::GFSAbi($page,'<body','>');
		$page=str_replace($body_tag,$body_tag.'<div class="'.CA::getAdminScreenClass().'" style="background:transparent">'.$apanel.'<div style="margin-left:205px">',$page);
		$page=str_replace('</body','</div></div></body',$page);
		$page=str_replace('</title>','</title>'.F_LF.'<link type="text/css" href="'.$rel_path.'documents/ca.css" rel="stylesheet">',$page);
		return $page;
	}

	public static function filterParamsToQuery(&$where,$params)
	{
		foreach($params as $pk=> $pv)
			if($pv!='')
				$where.=($where==''?' WHERE ':' AND ').' '.$pk.' LIKE "%'.$pv.'%" ';
	}

	public static function hideFromGuests(&$content)
	{
		global $user;
		if(strpos($content,'%hidden_text(')!==false)
		{
			$hid_cnt=self::GFS($content,'%hidden_text(',')%');
			if($user->userCookie()||Cookie::isAdmin())
				$content=str_replace('%hidden_text('.$hid_cnt.')%',$hid_cnt,$content);
			else
				$content=str_replace('%hidden_text('.$hid_cnt.')%','',$content);
		}
	}

	public static function parseMailMacros($str,$user_data=array(),$more_macros=array(),$get_perm_mcs=false)
	{
		$ip=Detector::getIP();
		$perm_macros_array=array('%ip%','%host%','%useremail%','%date%','%os%','%username%','%site%','%whois%','##');

		if($get_perm_mcs)
			return $perm_macros_array;

		$ca_site_url=str_replace('documents/centraladmin.php','',Linker::buildSelfURL('centraladmin.php'));

		$perm_macros_vals=array($ip,(isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:""),$user_data['email'],
			date('Y-m-d G:i',Date::tzone(time())),(isset($_SERVER['HTTP_USER_AGENT'])?Detector::defineOS($_SERVER['HTTP_USER_AGENT']):""),
			$user_data['username'],$ca_site_url,'http://en.utrace.de/?query='.$ip,'<br>');

		$str=str_replace('%%','%',$str); //backwards compatibility
		$str=self::strIReplace($perm_macros_array,$perm_macros_vals,$str);
//message specific macros
		if(is_array($more_macros))
			foreach($more_macros as $k=> $v)
				$str=self::strIReplace($k,$v,$str);
		//replacing the user data (if provided)
		if(is_array($user_data))
			foreach($user_data as $k=> $v)
				If(!is_array($v))
					$str=str_replace('%'.$k.'%',$v,$str);

		return $str;
	}

	public static function objDivReplacing($object,$replace_in)
	{
		$replace_in=str_replace("<p>$object</p>","<div>$object</div>",$replace_in);
		$replace_in=str_replace('<p class="rvps1">'.$object.'</p>','<div class="rvps1">'.$object.'</div>',$replace_in);
		$replace_in=str_replace('<p class="rvps2">'.$object.'</p>','<div class="rvps2">'.$object.'</div>',$replace_in);
		return $replace_in;
	}

	public static function objClearing($object,$replace_in)
	{
		$replace_in=str_replace("%".$object."(</p>","%".$object."(",$replace_in);
		$replace_in=str_replace("%".$object."(</span>","%".$object."(",$replace_in);
		$replace_in=str_replace("<span>)%",")%",$replace_in);
		$replace_in=str_replace('<p class="rvps1">)%',")%",$replace_in);
		$replace_in=str_replace('<p class="rvps2">)%',")%",$replace_in);

		while(strpos($replace_in,'%COPY(')!==false)
		{
			$m=self::GFSAbi($replace_in,'%COPY(',')%');
			$n=self::GFS($m,'%COPY(',')%');
			$replace_in=str_replace($m,'%COPY['.$n.']%',$replace_in);
		}
		return $replace_in;
	}

	public static function pTagClearing($replace_in)
	{
		$pos_p=strpos($replace_in,'<p');
		$pos_cp=strpos($replace_in,'</p>');
		if((($pos_cp!==false)&&($pos_p!==false)&&($pos_cp<$pos_p))||(($pos_cp!==false)&&($pos_p===false)))
		{
			$temp1=substr($replace_in,0,$pos_cp);
			$temp2=substr($replace_in,$pos_cp+4);
			$replace_in=$temp1.$temp2;
		}

		return $replace_in;
	}

	public static function dataSorting($records,$by_field='Id',$flag='desc',$prior_field=-1) // sorting info by date
	{
		if(!empty($records))
		{
			foreach($records as $key=> $row)
			{
				$ids[$key]=$row[$by_field];
				if($prior_field>0)
					$priors[$key]=$row[$prior_field];
			}
			if($prior_field>0)
			{
				if($flag=='desc')
					array_multisort($priors,SORT_DESC,SORT_NUMERIC,$ids,SORT_DESC,SORT_NUMERIC,$records);
				else
					array_multisort($priors,SORT_DESC,SORT_NUMERIC,$ids,SORT_ASC,SORT_NUMERIC,$records);
			}
			else
			{
				if($flag=='desc')
					array_multisort($ids,SORT_DESC,SORT_NUMERIC,$records);
				else
					array_multisort($ids,SORT_ASC,SORT_NUMERIC,$records);
			}
		}
		return $records;
	}

	public static function clearHtml($html)
	{

		if($html=='')
			return '';
		$html=str_replace(self::GFSAbi($html,'<div id="bkc"','</div>'),'',$html);
		$html=str_replace(self::GFSAbi($html,'<div id="bkf"','</div>'),'',$html);
		$html=Filter::imgAltTag($html);
		$search_main=array("'<\?php.*?\?>'si","'<script[^>]*?>.*?</script>'si","'<!--footer-->.*?<!--/footer-->'si","'<!--search-->.*?<!--/search-->'si","'<!--counter-->.*?<!--/counter-->'si","'<!--mmenu-->.*?<!--/mmenu-->'si","'<!--smenu-->.*?<!--/smenu-->'si","'<!--ssmenu-->.*?<!--/ssmenu-->'si","'<!--rand-->.*?<!--/rand-->'si","'<!--login-->.*?<!--/login-->'si");
		$result=preg_replace($search_main,array("","","","","","","","","",""),$html);

		if(!isset(self::$f->temp_erea_counter))
			self::$f->temp_erea_counter=1;
		if(strpos($result,'<div style="display:none" class="area1_x">')!==false)
			$result=preg_replace("'<!--%areap.*?<!--areaend-->'si","",$result);
		elseif(self::$f->temp_erea_counter>1)
			$result=preg_replace("'<!--%areap.*?<!--areaend-->'si","",$result);
		self::$f->temp_erea_counter++;

		$search_more=array("'<img.*?>'si","'<a .*?>'si","'<embed.*?</embed>'si","'<object.*?</object>'si","'<select[^>]*?>.*?</select>'si","'<[/!]*?[^<>]*?>'si","'\n'","'\r\n'","'&(quot|#34);'i","'&(amp|#38);'i","'&(lt|#60);'i","'&(gt|#62);'i","'&(nbsp|#160);'i","'&(iexcl|#161);'i","'&(cent|#162);'i","'&(pound|#163);'i","'&(copy|#169);'i","'&#(d+);'e","'%%USER.*?%%'si","'%%HIDDEN.*?HIDDEN%%'si","'%%DLINE.*?DLINE%%'si","'%%KEYW.*?%%'si");
		$replace_more=array(" "," "," "," "," "," "," "," ","\"","&","<",">"," ",chr(161),chr(162),chr(163),chr(169),"chr(\1)","","","","");
		$result=preg_replace($search_more,$replace_more,$result);
		$result=str_replace('%%TEMPLATE1%%','',$result);
		return self::esc($result);
	}

	public static function clearMacros($content,$id,$fields=array())
	{
		if($id=='136') //calendar
			$result=preg_replace(array("'%CALENDAR_OBJECT\(.*?\)%'si","'%CALENDAR_EVENTS\(.*?\)%'si","'%CALENDAR_.*?%'si"),array('','',''),$content);
		elseif($id=='137') //blog
			$result=preg_replace(array("'%BLOG_OBJECT\(.*?\)%'si","'%BLOG_ARCHIVE\(.*?\)%'si","'%BLOG_RECENT_COMMENTS\(.*?\)%'si","'%BLOG_RECENT_ENTRIES\(.*?\)%'si","'%BLOG_CATEGORY_FILTER\(.*?\)%'si","'%BLOG_.*?%'si"),array('','','','','',''),$content);
		elseif($id=='138')  //photoblog
			$result=preg_replace(array("'%BLOG_OBJECT\(.*?\)%'si","'%BLOG_EXIF_INFO\(.*?\)%'si","'%ARCHIVE_.*?%'si","'%BLOG_.*?%'si","'%PERIOD_.*?%'si","'%CATEGORY_.*?%'si","%GALLERY_LINK%","%CALENDAR%","'%BLOG_RECENT_COMMENTS\(.*?\)%'si","'%BLOG_RECENT_ENTRIES\(.*?\)%'si"),array('','','','','','','','','',''),$content);
		elseif($id=='143') //podcast
			$result=preg_replace(array("'%PODCAST_OBJECT\(.*?\)%'si","'%PODCAST_ARCHIVE\(.*?\)%'si","'%PODCAST_RECENT_COMMENTS\(.*?\)%'si","'%PODCAST_RECENT_EPISODES\(.*?\)%'si","'%PODCAST_CATEGORY_FILTER\(.*?\)%'si","'%PODCAST_OBJECT\(.*?\)%'si","'%PODCAST_.*?%'si"),array('','','','','','',''),$content);
		elseif($id=='144')  //guestbook
		{
			$content=preg_replace(array("'%GUESTBOOK_OBJECT\(.*?\)%'si","'%GUESTBOOK_ARCHIVE\(.*?\)%'si","'%GUESTBOOK_ARCHIVE_VER\(.*?\)%'si","'%GUESTBOOK_.*?%'si"),array('','','',''),$content);
			$result=str_replace(array('%HOME_LINK%','%HOME_URL%'),array('',''),$content);
		}
		elseif(in_array($id,array('181','192','191','190')))  //lister
		{
			$a=array_fill(0,17,'');
			$content=preg_replace(array("'%HASH\(.*?\)%'si","'%ITEMS\(.*?\)%'si","'%SCALE\(.*?\)%'si","'%SHOP_ITEM_DOWNLOAD_LINK\(.*?\)%'si","'%SHOP_CATEGORYCOMBO\(.*?\)%'si","'%SHOP_PREVIOUS\(.*?\)%'si","'%SHOP_NEXT\(.*?\)%'si","'%LISTER_CATEGORYCOMBO\(.*?\)%'si","'%LISTER_PREVIOUS\(.*?\)%'si","'%LISTER_NEXT\(.*?\)%'si","'<!--menu_java-->.*?<!--/menu_java-->'si","'<!--scripts2-->.*?<!--endscripts-->'si","'<!--<pagelink>/.*?</pagelink>-->'si","'<LISTER_BODY>.*?</LISTER_BODY>'si","'<LISTERSEARCH>.*?</LISTERSEARCH>'si","'<SHOP_BODY>.*?</SHOP_BODY>'si","'<SHOPSEARCH>.*?</SHOPSEARCH>'si","'%SHOP_.*?%'si","'%LISTER_.*?%'si","'%SLIDESHOWCAPTION_.*?%'si"),$a,$content);
			$content=str_replace(array('%ERRORS%','%IDEAL_VALID%','%QUANTITY%','%LINETOTAL%','%LINETOTAL%','%URL=Detailpage%','%CATEGORY_COUNT%','%SEARCHSTRING%','%SUBCATEGORIES%','%NAVIGATION% '),'',$content);

			$a=array_fill(0,40,'');
			$result=str_replace(array('<ITEM_VARS>','</ITEM_VARS>','<ITEM_VARS_LINE>','</ITEM_VARS_LINE>','<ITEM_HASHVARS>','</ITEM_HASHVARS>','<SHOP_DELETE_BUTTON>','</SHOP_DELETE_BUTTON>','<MINI_CART>','</MINI_CART>','<SHOP_BUY_BUTTON>','</SHOP_BUY_BUTTON>','<QUANTITY>','<RANDOM>','</RANDOM>','<SHOP>','</SHOP>','<LISTER>','</LISTER>','<ITEM_INDEX>','<ITEM_ID>','<ITEM_QUANTITY>','<ITEM_AMOUNT>','<ITEM_AMOUNT_IDEAL>','<ITEM_VAT>','<ITEM_SHIPPING>','<ITEM_CODE>','<ITEM_SUBNAME>','<ITEM_SUBNAME1>','<ITEM_SUBNAME2>','<ITEM_NAME>','<ITEM_CATEGORY>','<ITEM_VARS>','</ITEM_VARS>','<SHOP_URL>','<BANKWIRE>','</BANKWIRE>','<CATEGORY_HEADER>','</CATEGORY_HEADER>','<FROMCART>'),$a,$content);
		}
		else
			$result=$content;
		if(!empty($fields))
		{
			foreach($fields as $v)
				$result=str_replace('%'.$v.'%','',$result);
		}

		$result=str_replace(array('%LINK_TO_ADMIN%','%TAGS_CLOUD%','%FLASH_TAGS_CLOUD%'),array('','',''),$result);
		return $result;
	}

	public static function GFS($src,$start,$stop)
	{
		if($start=='')
			$res=$src;
		else if(strpos($src,$start)===false)
		{
			$res='';
			return $res;
		}
		else
			$res=substr($src,strpos($src,$start)+strlen($start));
		if(($stop!='')&&(strpos($res,$stop)!==false))
			$res=substr($res,0,strpos($res,$stop));
		return $res;
	}

	public static function GFSAbi($src,$start,$stop)
	{
		$res2=self::GFS($src,$start,$stop);
		return $start.$res2.$stop;
	}

	public static function mySubstr($string,$start,$stop,$utf_date_flag=false)
	{
		if(self::$f->use_mb)
			return mb_substr($string,$start,$stop,'UTF-8');
		else
		{
			$c=$string;
			$f=ord($c[0]);
			$nb=$stop;
			if($f>=0&&$f<=127)
				$nb=$stop;
			if($f>=192&&$f<=223&&!$utf_date_flag)
				$nb=$stop;
			if($f>=192&&$f<=223&&$utf_date_flag)
				$nb=$stop*2;
			if($f>=224&&$f<=239&&$utf_date_flag)
				$nb=$stop*3;
			if($f>=240&&$f<=247&&$utf_date_flag)
				$nb=$stop*4;
			if($f>=248&&$f<=251&&$utf_date_flag)
				$nb=$stop*5;
			if($f>=252&&$f<=253&&$utf_date_flag)
				$nb=$stop*6;
			return substr($string,$start,$nb);
		}
	}

	public static function substrUni($str,$s,$l=null)
	{
		return join("",array_slice(preg_split("//u",$str,-1,PREG_SPLIT_NO_EMPTY),$s,$l));
	}

	public static function strToLower($s)
	{
		return (self::$f->uni&&self::$f->use_mb)?mb_strtolower($s,"UTF-8"):strtolower($s);
	}

	public static function strToUpper($s)
	{
		return (self::$f->uni&&self::$f->use_mb)?mb_strtoupper($s,"UTF-8"):strtoupper($s);
	}

	public static function splitHtmlContent($string,$max_chr)
	{
		return Unknown::xtract($string,$max_chr/4);
	}

	public static function unEsc($s)
	{
		return str_replace(array('\\\\','\\\'','\"'),array('\\','\'','"'),$s);
	}

	public static function esc($s)
	{
		return (get_magic_quotes_gpc()?$s:str_replace(array('\\','\'','"'),array('\\\\','\\\'','\"'),$s));
	}

	public static function sth($s)
	{
		return htmlspecialchars(str_replace(array('\\\\','\\\'','\"'),array('\\','\'','"'),$s),ENT_QUOTES);
	}

	public static function sth2($s)
	{
		return str_replace(array('\\\\','\\\'','\"','<?','?>'),array('\\','\'','"','&lt;?','?&gt;'),$s);
	}

	public static function sth3($s)
	{
		return str_replace(array('\\\\','\\\'','\"'),array('\\','\'','"'),$s);
	}

	public static function stripTags($src,$tags='')
	{
		$src=urldecode($src);
		$src=strip_tags($src,$tags);
		return $src;
	}

	public static function stripQuotes($src)
	{
		$src=str_replace(array('"','\''),'',$src);
		return $src;
	}

	# formats admin screen output


	public static function fmtAdminScreen($content,$menu='')
	{
		$output='<div class="'.CA::getAdminScreenClass().'">';
		if(!empty($menu))
			$output.=$menu.'<br class="ca_br" />';
		$output.=$content.'</div>';
		return $output;
	}

	public static function fmtBlockedIPs($blocked_ips,$script_path,$unblock_label,$noblocked_label)
	{
		global $c_page_amp,$lg_amp;

		if(!empty($blocked_ips))
		{
			$output='<div class="a_n"><div class="a_navn"><table class="'.self::$f->atbgr_class.'" cellspacing="1" cellpadding="4">';
			foreach($blocked_ips as $v)
			{
				$output.='<tr>
					<td><span class="rvts8">'.$v['ip'].'</span></td>
					<td><a class="rvts12" href="'.$script_path."?action=index&amp;unblockip=".$v['ip'].$c_page_amp.$lg_amp.'">['.$unblock_label.']</a></td></tr>';
			}
			$output.='</table></div></div>';
		}
		else
			$output='<span class="rvts8 empty_caption">'.$noblocked_label.'</span>';
		return $output;
	}

	# formats page output in template
	public static function fmtInTemplate($filename,$page_output,$css='',$bg_tag='',$include_menu=true,$include_counter=false,$miniform_in_earea=false,$grab_tpl_from_php=false,$ignore_fullScreen=false)
	{
		$root=!(((strpos($filename,'../')!==false)&&substr_count($filename,'/')>1&&(strpos(self::$f->template_source,'../')===false)));
		if(!$root)
			self::$f->template_source='../'.self::$f->template_source;
		if(file_exists(self::$f->template_source))
			$filename=self::$f->template_source;

		$contents=File::read($filename);
		$fs=$ignore_fullScreen?false:self::$f->ca_fullscreen;
		if($grab_tpl_from_php) //get template from php page (remove all the php code)
			$contents=str_replace(self::GFSAbi($contents,'<?php','?>'),'',$contents);

		if(!$fs&&strpos($filename,'template_source.html')!==false&&strpos($contents,'%CONTENT%')!==false)
			$pattern='%CONTENT%';
		elseif(!$fs&&strpos($contents,'<!--page-->')!==false&&$include_menu)
			$pattern=self::GFS($contents,'<!--page-->','<!--/page-->');
		else
		{
			$pattern=self::GFSAbi($contents,'<body','</body>');
			$body_part=substr($pattern,0,strpos($pattern,'>')+1);
			if($bg_tag!=='')
				$body_part=str_replace('<body','<body style="'.$bg_tag.'"',$body_part);
			$page_output=$body_part.'<!--page-->'.$page_output.'<!--/page--></body>';
		}
		$contents=str_replace($pattern,$page_output,$contents);
		if($include_counter==false)
			$contents=str_replace(self::GFS($contents,'<!--counter-->','<!--/counter-->'),'',$contents);
		if(!empty($css))
			$contents=str_replace('<!--scripts-->','<!--scripts-->'.F_LF.$css,$contents);
		if($root&&(strpos($filename,'template_source.html')!==false)&&!$miniform_in_earea)
			$contents=str_replace(array('src="../','href="../'),array('src="','href="'),$contents);
		if($fs)
			$contents=str_replace('documents/textstyles_nf.css"','documents/ca.css"',$contents);
		else
			$contents=str_replace('</title>','</title>'.F_LF.'<link type="text/css" href="'.($root&&!$miniform_in_earea?'':'../').'documents/ca.css" rel="stylesheet">',$contents);

		if(self::$f->ca_fullscreen&&strpos($contents,'script.js')!==false&&strpos($contents,'art-sheet')===false) //fix for conflict artisteer in full screen mode
			$contents=str_replace('<script type="text/javascript" src="documents/script.js"></script>','',$contents);

		$contents=str_replace('<!--scripts-->','<!--scripts--><script type="text/javascript" src="'.($root?'':'../').'documents/ca.js"></script>',$contents);

    if(strpos($contents,'class="color')!==false)
			$contents=str_replace('<!--scripts-->','<!--scripts--><script type="text/javascript" src="'.($root?'':'../').'js/jscolor.js"></script>',$contents);


		return $contents;
	}

	public static function fmtErrMsg($msg)
	{
		return '<div class="a_n a_navtop">
			<p><span class="rvts8"><em style="color:red;">'.$msg.'</em></span></p>'.F_BR.'</div>'.F_BR;
	}

	public static function fmtErrorMsg($error_index,$affected_file='')
	{
		$template='<h1>%s</h1>
			<h1>%s</h1>
			<span>%s</span>';

		if($error_index=='EMAIL_NOTSET')
			$output=sprintf($template,'Email FAILED','PROBLEM: You haven\'t defined your email yet',
				'To solve the problem, open page in EZGenerator and type email address in Send Notification to box.');
		elseif($error_index=='MAIL_FAILED')
			$output=sprintf($template,'Operation FAILED!','PROBLEM: Missing mail settings','To solve the problem, check with host provider if server uses MAIL or SMTP for sending mails.
			If SMTP is used, get the smtp settings from provider, go to Project Settings - PHP settings and set the smtp settings.
			If MAIL is used, check with your provider if mail settings are set correctly.');
		elseif($error_index=='MISSING_DBFILE')
			$output=sprintf($template,'Operation FAILED!','Database file '.$affected_file.' is missing on server.',
				'To solve the problem, go to Project Settings - Upload, press Re-upload database button and then Publish.');
		elseif($error_index=='DBFILE_NEEDCHMOD')
			$output=sprintf($template,'Operation FAILED!','Database file '.$affected_file.' doesn\'t have WRITE permissions.',
				'Set file permissions manually on server. If server is LINUX, set CHMOD 666. If server is WINDOWS, set WRITE permission.');
		return $output;
	}

	public static function includeMetaArchives($page_src,$archive_entries)
	{

		$meta='';
		foreach($archive_entries as $v)
			$meta.='<link rel="archives" title="'.$v['title'].'" href="'.$v['href'].'">'.F_LF;
		$page_src=str_replace('<!--rss_meta-->','<!--rss_meta-->'.F_LF.$meta,$page_src);
		return $page_src;
	}

	public static function floatLogin($src)
	{
		$temp=self::GFS($src,'<!--login-->','<!--/login-->');
		$float_login=strpos($temp,'class="frm_login"')!==false;
		return $float_login;
	}

	public static function titleForLink($title)
	{
		$title=str_replace(' ','-',self::stripTags(self::strToLower(self::sth2(urldecode($title)))));
		return rawurlencode(str_replace(array('&','@','"',"'",'/',':',';',',','?','.','!','$','|','<','>','=','^','#','\\'),'',$title));
	}

}

class CommentHandler extends FuncHolder
{

	public static function getTagAttr($string,$tag,$attr,$has_closing=false)
	{
		$pattern='/<'.$tag.' (.*?)'.$attr.'=((\'(.*?)\')|("(.*?)"))(.*?)'.($has_closing?'>(.*?)</'.$tag.'>':'(\/)?>').'/i';
		preg_match_all($pattern,$string,$tagAttrs,PREG_PATTERN_ORDER);
		$ret=array();
		foreach($tagAttrs[1] as $pos=> $tag)
		{
			if($tagAttrs[4][$pos]!='')
				$ret[]=$tagAttrs[4][$pos];
			elseif($tagAttrs[6][$pos]!='')
				$ret[]=$tagAttrs[6][$pos];
		}

		return $ret;
	}

	public static function parseComment($str,$full_access,$loggedUser,$canUseUrl=false)
	{

		$htmlTags=($full_access?implode('',self::$f->comments_allowed_tags['html_admin']):'').implode('',self::$f->comments_allowed_tags['html']);
		if($loggedUser)
			$htmlTags .= implode('',self::$f->comments_allowed_tags['extra']);
		$result=strip_tags($str,$htmlTags);
		$result=self::cleanInsideHtmlTags($result,implode('',self::$f->comments_allowed_tags['html']));
		if($loggedUser)
			$result=self::parseTagsWithAttrs($result,self::$f->comments_allowed_tags['extra']);
		if($canUseUrl)
			self::parseContentX($result);

		return $result;
	}

	public static function parseContentX(&$str)
	{
		if(isset($_POST['content_x'])&&$_POST['content_x']!='')
			$str .= $_POST['content_x'];
	}

	//Clean the inside of the tags
	public static function cleanInsideHtmlTags($str,$tags)
	{
		preg_match_all('/<([^>]+)>/i',$tags,$allTags,PREG_PATTERN_ORDER);
		foreach($allTags[1] as $tag)
		{
			$str=preg_replace('/<'.$tag.' [^>]*>/i','<'.$tag.'>',$str);
		}

		return $str;
	}

	public static function parseTagsWithAttrs($str,$allowed_tags)
	{
		$allCTags=array();
		$allNTags=array();
		preg_match_all('/<([^>]+)>(.*?)<\/([^>]+)>/i',$str,$allCTags,PREG_PATTERN_ORDER);
		preg_match_all('/<([^>]+)>/i',$str,$allNTags,PREG_PATTERN_ORDER);
		foreach($allCTags[1] as $pos=> $tagInfo)
		{
			$tag=strtoupper($allCTags[3][$pos]);
			if(in_array('<'.$tag.'>',$allowed_tags))
			{
				if($tag=='A')
				{
					$url=$allCTags[2][$pos];
					if(strpos($tagInfo,'=')!==false)
						$url=self::getTagAttr($tagInfo,'a','href');
					$str=str_replace($allCTags[0][$pos],'<a href="'.$url.'">'.$allCTags[2][$pos].'</a>',$str);
				}
			}
		}

		foreach($allNTags[0] as $pos=> $tagInfo)
			if(strpos($tagInfo,'<img')!==false)
			{
				$imgScrs=self::getTagAttr($tagInfo,'img','src');
				if(!empty($imgScrs))
				{
					$imgSrc=Linker::relPathBetweenURLs($imgScrs[0],Linker::currentPageUrl());
					$str=str_replace($tagInfo,'<img class="img_comment_maxw" src="'.$imgSrc.'" />',$str);
				}
			}
			elseif(strpos($tagInfo,'<span')!==false)
			{
				$spanStyles=self::getTagAttr($tagInfo,'span','style');
				if(!empty($spanStyles))
					$str=str_replace($tagInfo,'<span style="'.$spanStyles[0].'" />',$str);
			}
			elseif(strpos($tagInfo,'<div')!==false)
			{
				$divStyles=self::getTagAttr($tagInfo,'span','style');
				if(!empty($divStyles))
					$str=str_replace($tagInfo,'<span style="'.$divStyles[0].'" />',$str);
			}
		return $str;
	}

	public static function buildHintDiv($lang_l,$admin=false)
	{
		$hint='';
		$htmlTags=array_merge(self::$f->comments_allowed_tags['html'],self::$f->comments_allowed_tags['extra']);
		if($admin)
			$htmlTags=array_unique(array_merge($htmlTags,self::$f->comments_allowed_tags['html_admin']));

		foreach($htmlTags as $tag)
		{
			if($tag=='<a>')
				$hint.= '<span class="comment_tag_lbl rvts12" title="'.htmlspecialchars('<a href="http://some.url"></a>').'">'.htmlspecialchars($tag).'</span>&nbsp;';
			elseif($tag=='<img>')
				$hint.= '<span class="comment_tag_lbl rvts12" title="'.htmlspecialchars('<img src="http://some.url" >').'">'.htmlspecialchars($tag).'</span>&nbsp;';
			else
				$hint.= '<span class="comment_tag_lbl rvts12" title="'.htmlspecialchars($tag.str_replace('<','</',$tag)).'">'.htmlspecialchars($tag).'</span>&nbsp;';
		}

		return '<div class="rvts8 allowed_tags">'.$lang_l['comments tags allowed'].$hint.'</div>';
	}
}

class Video extends FuncHolder
{

	public static function getVideoImage($url)
	{
		$image_url=parse_url($url);
		if($image_url['host']=='www.youtube.com'||$image_url['host']=='youtube.com')
		{
			$array=explode('&',$image_url['query']);
			return 'http://i3.ytimg.com/vi/'.substr($array[0],2).'/default.jpg';
		}
		else if($image_url['host']=='www.youtu.be'||$image_url['host']=='youtu.be')
		{
			$array=explode('/',$image_url['path']);
			return 'http://i3.ytimg.com/vi/'.$array[1].'/default.jpg';
		}
		else if($image_url['host']=='www.vimeo.com'||$image_url['host']=='vimeo.com')
		{
			$ctx=stream_context_create(array('http'=>array('timeout'=>5)));
			$hash=unserialize(file_get_contents(
					'http://vimeo.com/api/v2/video/'.substr($image_url['path'],1).'.php',false,$ctx));
			return $hash[0]["thumbnail_small"];
		}
	}

	public static function youtube_vimeo_check($src)
	{
		$src=Formatter::strToLower($src);
		return strpos($src,'youtube.')!==false ||	strpos($src,'youtu.be')!==false || strpos($src,'.yimg/')!==false
				||strpos($src,'vimeo.com')!==false;
	}
}

class Image extends FuncHolder
{
//flags: thumb, image, rescalethumb, rescaleimage
//thumb_scale could be 1=default or 2=crop
	public static function scale($fname,$max_image_size=600,$flag='image',$quality=100,$max_image_side=600,$max_thumb_size=300,$max_thumb_height=120,$thumb_scale=1)  // scale image/thumbnail
	{
		if(ini_get('memory_limit')<100)
			ini_set('memory_limit','100M'); // fix for serevrs with low memory limit

		if($flag=='rescaleimage')
		{
			$full_fname=substr($fname,0,strrpos($fname,"."))."_full".substr($fname,strrpos($fname,"."));
			if(file_exists($full_fname))
				list($orig_width,$orig_height,$img_type,$img_attr)=@getimagesize($full_fname);
			else
				list($orig_width,$orig_height,$img_type,$img_attr)=@getimagesize($fname);
		}
		else
			list($orig_width,$orig_height,$img_type,$img_attr)=@getimagesize($fname);

		$thumb=$flag=='thumb'||$flag=='rescalethumb';
		$max_size_param=$thumb?$max_thumb_size:$max_image_size;
		if($orig_width>$max_size_param||$orig_height>$max_size_param)
		{
			if($flag=='image')
			{
				$new_fname=$fname;
				$fname=substr($fname,0,strrpos($fname,"."))."_full".substr($fname,strrpos($fname,"."));
				rename($new_fname,$fname);
			}
			elseif($flag=='rescaleimage')
			{
				$final_name=$fname;
				$new_fname=substr($fname,0,strrpos($fname,"."))."_tempimg".substr($fname,strrpos($fname,"."));
				if(file_exists($full_fname))
				{
					copy($full_fname,$fname);
				}
			}
			elseif($flag=='rescalethumb')
			{
				$final_name=substr($fname,0,strrpos($fname,"."))."_thumb".substr($fname,strrpos($fname,"."));
				$new_fname=substr($fname,0,strrpos($fname,"."))."_thumb_tempimg".substr($fname,strrpos($fname,"."));
			}
			else
			{
				$new_fname=substr($fname,0,strrpos($fname,"."))."_thumb".substr($fname,strrpos($fname,"."));
			}

			$ratio=$orig_width/$orig_height;

			if($thumb_scale==2)
				$scaling='crop';
			else
				$scaling='default';
//scaling_mode
			if(!$thumb||$scaling=='default') //old scaling
			{
				if($orig_width>=$orig_height)
				{
					$new_width=$max_size_param;
					$new_height=intval($max_size_param/$ratio);
				}
				else
				{
					$new_width=intval($max_size_param*$ratio);
					$new_height=$max_size_param;
				}
			}
			else
			{
				if($orig_width>=$orig_height)
				{
					$new_height=$max_thumb_height;
					$new_width=intval($new_height*$ratio);
				}
				else
				{
					$new_width=$max_size_param;
					$new_height=intval($new_width/$ratio);
				}
			}
// Resample
			$image_p=@imagecreatetruecolor($new_width,$new_height);
			$image=self::gdCreate($img_type,$fname);
//transparency
			if($img_type==1||$img_type==3)
			{
				$trnprt_indx=imagecolortransparent($image);
				if($trnprt_indx>=0)
				{
					$trnprt_color=imagecolorsforindex($image,$trnprt_indx);
					$trnprt_indx=imagecolorallocate($image_p,$trnprt_color['red'],$trnprt_color['green'],$trnprt_color['blue']);
					imagefill($image_p,0,0,$trnprt_indx);
					imagecolortransparent($image_p,$trnprt_indx);
				}
				elseif($img_type==3)
				{
					imagealphablending($image_p,false);
					$color=imagecolorallocatealpha($image_p,0,0,0,127);
					imagefill($image_p,0,0,$color);
					imagesavealpha($image_p,true);
				}
			}
//end transparency
			if($image!='')
			{
				imagecopyresampled($image_p,$image,0,0,0,0,$new_width,$new_height,$orig_width,$orig_height);

				self::gdSave($image_p,$new_fname,$quality,$img_type); // Save image
				imagedestroy($image_p);
				imagedestroy($image);
				if($flag=='rescalethumb'||$flag=='rescaleimage')
				{
					unlink($final_name);
					rename($new_fname,$final_name);
				}
				return $new_fname;
			}
			else
				return false;
		}
		elseif($flag=='image')
		{
			$full_fname=substr($fname,0,strrpos($fname,"."))."_full".substr($fname,strrpos($fname,"."));
			copy($fname,$full_fname);
			return $fname;
		}
		else
			return $fname;
	}

	public static function gdSave($image_p,$new_fname,$quality,$img_type)
	{
		if($img_type==1)
		{
			if(function_exists("imagegif"))
				imagegif($image_p,$new_fname);
			elseif(function_exists("imagejpeg"))
				imagejpeg($image_p,$new_fname,$quality);
			else
				imagepng($image_p,$new_fname);
		}
		elseif($img_type==3)
		{
			imagepng($image_p,$new_fname);
		}
		else
		{
			if(function_exists("imagejpeg"))
				imagejpeg($image_p,$new_fname,$quality);
			elseif(function_exists("imagegif"))
				imagegif($image_p,$new_fname);
			else
				imagepng($image_p,$new_fname);
		}
	}

	public static function gdCreate($img_type,$fname)
	{
		if($img_type==1)
		{
			if(function_exists("imagecreatefromgif"))
				$image=imagecreatefromgif($fname);
			elseif(function_exists("imagecreatefromjpeg"))
				$image=imagecreatefromjpeg($fname);
			else
				$image=imagecreatefrompng($fname);
		}
		elseif($img_type==3)
			$image=imagecreatefrompng($fname);
		else
		{
			if(function_exists("imagecreatefromjpeg"))
				$image=imagecreatefromjpeg($fname);
			elseif(function_exists("imagecreatefromgif"))
				$image=imagecreatefromgif($fname);
			else
				$image=imagecreatefrompng($fname);
		}
		return $image;
	}

	public static function gdRotate($fname,$quality,$rotate_angle)
	{
		if(ini_get('memory_limit')<50)
			ini_set('memory_limit','50M');

		list($orig_width,$orig_height,$img_type,$img_attr)=@getimagesize($fname);
		if($rotate_angle>0&&function_exists("imagerotate"))
		{
			$image_r=self::gdCreate($img_type,$fname);
			if($image_r!='')
			{
				$image_r_new=imagerotate($image_r,intval($rotate_angle),0);
				self::gdSave($image_r_new,$fname,$quality,$img_type);
			}
		}
	}

	public static function buildYTImage($yt_url)
	{
		if(strpos($yt_url,'embed/')!==false)
			$id=Formatter::GFS($yt_url,'embed/','');
		elseif(strpos($yt_url,'watch?v=')!==false&&strpos($yt_url,'&')===false)
			$id=substr($yt_url,strpos($yt_url,'?v=')+3);
		elseif(strpos($yt_url,'watch?v=')!==false)
			$id=Formatter::GFS($yt_url,'?v=','&');
		elseif(strpos($yt_url,'?')!==false)
			$id=Formatter::GFS($yt_url,'/v/','?');
		elseif(strpos($yt_url,'&')!==false)
			$id=Formatter::GFS($yt_url,'/v/','&');
		else
			$id=substr($yt_url,strpos($yt_url,'/v/')+3);
		return 'http://img.youtube.com/vi/'.$id.'/0.jpg';//return 'http://i1.ytimg.com/vi/'.$id.'/default.jpg';
	}

}

class RSS extends FuncHolder
{

	public static function clearCache($script_dir)
	{
		$files=array();
		if($handle=opendir($script_dir.'innovaeditor/assets/'))
		{
			while(false!==($file=readdir($handle)))
			{
				if($file!="."&&$file!=".."&&strpos($file,'cache_')===0)
					$files[]=$file;
			}
		}
		closedir($handle);
		foreach($files as $v)
			unlink($script_dir.'innovaeditor/assets/'.$v);
	}

	public static function line($tag,$rss_setting,$fl_flag=false,$sth=false)
	{
		$t=($fl_flag)?' ':'';
		return $t."<$tag>".($sth?Formatter::sth($rss_setting):$rss_setting)."</$tag>".F_LF;
	}

	public static function lineSt($line,$fl_flag=false)
	{
		$t=($fl_flag)?' ':'';
		return $t.$line.F_LF;
	}

	public static function buildHeader($rss_settings,$page_charset,$page_url,$publish_date,$more_xmlns='',$fl_flag=false,$rss_url='',$title='',$googleM=false)
	{
		if($googleM)
			$rss_header='<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
		elseif(!isset($rss_settings['Subtitle (iTunes)']))
			$rss_header='<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" '.$more_xmlns.'>';
		else
			$rss_header='<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" '.$more_xmlns.'>';
		$pub_date=date('r',$publish_date);
		if($title=='')
			$title=empty($rss_settings['Title'])?'My site':$rss_settings['Title'];

		$output='<?xml version="1.0" encoding="'.$page_charset.'"?>'.F_LF;
		if(isset($_SERVER['HTTP_USER_AGENT'])&&strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')!==false)
			$output.='<?xml-stylesheet type="text/xsl" media="screen" href="'.self::$f->site_url.'documents/rss.xls"?>'.F_LF;
		$output.=self::lineSt($rss_header,$fl_flag).self::lineSt('<channel>',$fl_flag);
		if(strpos($rss_header,'xmlns:atom')!==false)
			$output.=self::lineSt('<atom:link href="'.($rss_url!=''?$rss_url:$page_url.'?action=rss').'" rel="self" type="application/rss+xml"/>',$fl_flag);
		$output.=self::line('title',$title,$fl_flag,true).self::line('link',$page_url,$fl_flag)
			.self::line('description',$rss_settings['Description'],$fl_flag,true)
			.self::line('language',$rss_settings['Language'],$fl_flag).self::line('pubDate',$pub_date,$fl_flag).self::line('lastBuildDate',$pub_date,$fl_flag)
			.self::line('docs','http://blogs.law.harvard.edu/tech/rss',$fl_flag);

		$tags_list=array('copyright','managingEditor','webMaster','category','ttl','cloud','image','rating','textInput','skipHours','skipDays');
		$settings_list=array('Copyright','Managing editor','Webmaster','Category','TTL','Cloud domain','Image','Rating','Text input title','Skip hours','Skip days');
		foreach($settings_list as $k=> $v)
		{
			if(!empty($rss_settings[$v]))
			{
				$tag=$tags_list[$k];
				$value=$rss_settings[$v];
				if($v=='Category'&&empty($rss_settings['Category domain']))
					$output.=self::line($tag,$value,$fl_flag,true);
				elseif($v=='Category')
					$output.=self::lineSt('<'.$tag.' domain="'.$rss_settings['Category domain'].'">'.$value.'</'.$tag.'>',$fl_flag,true);
				elseif($v=='TTL'&&$value!=0)
					$output.=self::line($tag,$value,$fl_flag,true);
				elseif($v=='Cloud domain')
					$output.=self::lineSt('<'.$tag.' domain="'.$value.'" port="'.$rss_settings['Cloud port'].'" path="'.$rss_settings['Cloud path'].'" registerProcedure="'.$rss_settings['Cloud reg proc'].'" protocol="'.$rss_settings['Cloud protocol'].'"/>',$fl_flag);
				elseif($v=='Image')
					$output.=self::lineSt('<'.$tag.'>',$fl_flag).self::lineSt('<title>'.$title.'</title>',$fl_flag).self::lineSt('<link>'.$page_url.'</link>',$fl_flag).self::lineSt('<url>'.$value.'</url>',$fl_flag).self::lineSt('</'.$tag.'>',$fl_flag);
				elseif($v=='Text input title')
					$output.=self::lineSt('<'.$tag.' title="'.$value.'" description="'.$rss_settings['Text input description']
						.'" name="'.$rss_settings['Text input name'].'" link="'.$rss_settings['Text input link'].'"></'.$tag.'>',$fl_flag);
				else
					$output.=self::line($tag,$value,$fl_flag,true);
			}
		}
// iTunes special tags
		if(isset($rss_settings['Subtitle (iTunes)']))
		{
			$tags_list=array('itunes:summary','itunes:subtitle','itunes:author','itunes:image','itunes:owner','itunes:keywords','itunes:explicit','itunes:block','itunes:new-feed-url');
			$settings_list=array('Description','Subtitle (iTunes)','Author (iTunes)','Image (iTunes)','Owner name (iTunes)','Keywords (iTunes)','Explicit (iTunes)','Block (iTunes)','New-feed-url (iTunes)');
			foreach($settings_list as $k=> $v)
			{
				$tag=$tags_list[$k];
				$value=$rss_settings[$v];
				if($v=='Description')
					$output.=self::line($tag,(empty($value)?'This is my podcast':$value),$fl_flag,true);
				elseif($v=='Owner name (iTunes)'&&(!empty($value)||!empty($rss_settings['Owner email (iTunes)'])))
				{
					$output.=self::lineSt('<'.$tag.'>',$fl_flag);
					if($rss_settings['Owner name (iTunes)']!='')
						$output.=self::line('itunes:name',$rss_settings['Owner name (iTunes)'],$fl_flag,true);
					if($rss_settings['Owner email (iTunes)']!='')
						$output.=self::line('itunes:email',$rss_settings['Owner email (iTunes)'],$fl_flag,true);
					$output.=self::lineSt('</'.$tag.'>',$fl_flag);
				}
				elseif(!empty($rss_settings[$v]))
				{
					if($v=='Image (iTunes)')
						$output.=self::lineSt('<'.$tag.' href="'.$value.'" />');
					else
						$output.=self::line($tag,$value,$fl_flag,true);
				}
			}
// iTunes categories
			$itunes_cats=array('Category (iTunes)','Category II (iTunes)','Category III (iTunes)');
			$itunes_subcats=array('Subcategory (iTunes)','Subcategory II (iTunes)','Subcategory III (iTunes)');
			foreach($itunes_cats as $k=> $cat)
			{
				$subcat=$itunes_subcats[$k];
				if(!empty($rss_settings[$cat])&&!empty($rss_settings[$subcat]))
				{
					$output.=self::lineSt('<itunes:category text="'.Formatter::sth($rss_settings[$cat]).'">',$fl_flag);
					$output.=self::lineSt('<itunes:category text="'.Formatter::sth($rss_settings[$subcat]).'" />',$fl_flag);
					$output.=self::lineSt('</itunes:category>',$fl_flag);
				}
				elseif(!empty($rss_settings[$cat]))
					$output.=self::lineSt('<itunes:category text="'.Formatter::sth($rss_settings[$cat]).'"/>',$fl_flag);
			}
		}
		return $output;
	}

	public static function buildItems($rss_data,$fl_flag=false)
	{
		$output='';
		if(!empty($rss_data))
		{
			foreach($rss_data as $item)
			{
				$output.=self::lineSt('<item>',$fl_flag);
				foreach($item as $tag=> $value)
				{
					if(!is_array($value))
					{
						if($tag=='guid')
							$output.=self::lineSt('<'.$tag.' isPermaLink="true">'.$value.'</'.$tag.'>',$fl_flag);
						else
							$output.=self::line($tag,$value,$fl_flag);
					}
					else
					{
						if($tag=='enclosure'||$tag=='media:content')
						{
							$line='<'.$tag;
							foreach($value as $attr=> $v)
								$line.=' '.$attr.'="'.$v.'"'; $line.='/>';
							$output.=self::lineSt($line,$fl_flag);
						}
						elseif($tag=='category')
							$output.=self::lineSt('<'.$tag.' domain="'.$value['domain'].'">'.$value['value'].'</'.$tag.'>',$fl_flag);
					}
				}
				$output.=self::lineSt('</item>',$fl_flag);
			}
		}
		return $output;
	}

	public static function build($rss_data,$rss_settings,$page_charset,$page_url,$publish_date,$more_xmlns='',$fl_flag=false,$rss_url=''
	,$title='',$googleM=false)
	{
		$output=self::buildHeader($rss_settings,$page_charset,$page_url,$publish_date,$more_xmlns,$fl_flag,$rss_url,$title,$googleM)
			.self::buildItems($rss_data,$fl_flag)
			.self::lineSt('</channel>',$fl_flag)
			.self::lineSt('</rss>',$fl_flag);
		return $output;
	}

}

//checks if given data is valid
class Validator extends FuncHolder
{

	public static function checkImgSrc($imgScr)
	{
		$imgExtsAllowed=array('JPG','JPEG','PNG','GIF');
		$imgFile=substr($imgScr,strrpos($imgScr,'/')+1);
		$imgExt=substr($imgFile,strpos($imgFile,'.')+1);
		return !(strpos($imgExt,'.')!==false||!in_array(Formatter::strToUpper($imgExt),$imgExtsAllowed));
	}

	public static function valdiateCommentsForm($name_field,$content_field,$email_field,$forbid_urls,$email_enabled,$require_email,$lang_uc,$blocked_ip=false,$must_be_logged=false,$used_in_blog_comments=false,$content_field_required=true)
	{
		global $thispage_id,$user;

		$ccheck=isset($_POST['cc'])&&$_POST['cc']=='1';

		$errors=array();
		$content=$_POST[$content_field];
		$name=(!is_array($name_field)?$_POST[$name_field]:'');
		$code_not_allowed=$used_in_blog_comments?false:(strlen($content)!==(strlen(strip_tags($content))));
		$invalid_img_url=false;
		if($used_in_blog_comments)
		{
			$imgSources=CommentHandler::getTagAttr($content,'img','src',false);
			foreach($imgSources as $imgScr)
			{
				$relImgScr=Linker::relPathBetweenURLs($imgScr,Linker::currentPageUrl());
				$content=str_replace($imgScr,$relImgScr,$content);
				if(!self::checkImgSrc($imgScr))
					$invalid_img_url=true;
			}
		}
		else
			$content=strip_tags($content);
		$name=strip_tags($name);

		$mail=(isset($_POST[$email_field]))?Formatter::stripTags($_POST[$email_field]):'';

		Session::intStart('private');

		$is_logged=Cookie::isAdmin()||$user->userCookie();
		$logged=($must_be_logged&&$is_logged);
		if($must_be_logged&&!$logged)
		{
			$errors[]='er_error|'.$lang_uc['login on comments'];
			return $errors;
		}

		$ct_dec=html_entity_decode($content);
		$content_invalid=($forbid_urls&&((strpos($ct_dec,'http')!==false)||(strpos($ct_dec,'href')!==false)||(strpos($ct_dec,'www.')!==false)));
		$mail_valid=(!$is_logged&&$email_enabled&&$require_email&&!self::validateEmail($mail))?false:true;

		if($name=='')
		{
			if(is_array($name_field))
			{
				foreach($name_field as $v)
				{
					if($_POST[$v]==''||$v=='country'&&$_POST[$v]=='Select')
						$errors[]=($ccheck?$v.'|':'').$lang_uc['Required Field'];
				}
			}
			else
				$errors[]=($ccheck?$name_field.'|':'').$lang_uc['Required Field'];
		}
		if($content_field_required&&$content=='')
			$errors[]=($ccheck?$content_field.'|':'').$lang_uc['Required Field'];
		elseif($code_not_allowed)
			$errors[]=($ccheck?'er_error|':'')."Not allowed to include HTML or other code!";
		elseif($content_invalid)
			$errors[]=($ccheck?'er_error|':'')."Not allowed to include url!";

		if($invalid_img_url)
			$errors[]=($ccheck?'er_error|':'')."Img src provided is not allowed !";

		if(!$mail_valid)
			$errors[]=($ccheck?$email_field.'|'.$lang_uc['Email not valid']:$lang_uc['Email not valid']);

		$captcha_invalid=(!isset($thispage_id)&&self::isAbleToBuildImg())?!Captcha::isValid('code'):false;
		if($captcha_invalid&&(Cookie::isAdmin()||$user->userCookie()))
			$captcha_invalid=false;
		if($captcha_invalid)
			$errors[]=($ccheck?'code|':'').$lang_uc['Captcha Message'];
		if($blocked_ip)
			$errors[]=($ccheck?'er_error|':'').$lang_uc['your IP is blocked'];
		elseif(!empty($errors))
			$errors[]=($ccheck?'er_error|':'').$lang_uc['validation failed'];

		return $errors;
	}

	public static function validateEmail($email)
	{
		$isValid=true;
		$atIndex=strrpos($email,"@");
		if(is_bool($atIndex)&&!$atIndex)
			$isValid=false;
		else
		{
			$domain=substr($email,$atIndex+1);
			$local=substr($email,0,$atIndex);
			$localLen=strlen($local);
			$domainLen=strlen($domain);
			if($localLen<1||$localLen>64)
				$isValid=false; // local part length exceeded
			else if($domainLen<1||$domainLen>255)
				$isValid=false; // domain part length exceeded
			else if($local[0]=='.'||$local[$localLen-1]=='.')
				$isValid=false; // local part starts or ends with '.'
			else if(preg_match('/\\.\\./',$local))
				$isValid=false;  // local part has two consecutive dots
			else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/',$domain))
				$isValid=false;// character not valid in domain part
			else if(preg_match('/\\.\\./',$domain))
				$isValid=false; // domain part has two consecutive dots
			else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local)))
			{ // character not valid in local part unless local part is quoted
				if(!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local)))
					$isValid=false;
			}
			if(function_exists('checkdnsrr')&&$isValid&&!(checkdnsrr($domain,"MX")||checkdnsrr($domain,"A")))
				$isValid=false;// domain not found in DNS
		}
		return $isValid;
	}

	public static function isAbleToBuildImg()
	{
		if(self::$f->captcha_size!='none' && function_exists('imagecreate')&&(function_exists('imagegif')||function_exists('imagejpeg')||function_exists('imagepng')))
			return true;
		else
			return false;
	}

	public static function checkProtection($page_info)  // returns: 1:unprotected, 2:protected, 3:partly protected, false:error
	{
		if(!is_array($page_info)||!isset($page_info[6]))
			return false;
		if($page_info[6]=='TRUE') //page is protected
		{
			if(isset($page_info[25])&&$page_info[25]=='PP')
				return 3;
			else
				return 2;
		}
		else
			return 1;
	}

}

class Captcha extends FuncHolder
{

	public static function isRecaptchaPosted()
	{
		return (isset($_POST['recaptcha_challenge_field'])&&isset($_POST['recaptcha_response_field']));
	}

	public static function isValid($inputName='captchacode')
	{
		$ccheck=isset($_POST['cc'])&&$_POST['cc']=='1'; //needed to know if it's check or post (not sure why it was outside before)
		//and also it's still outside for compatibility.
		if(self::$f->reCaptcha)  //we have reCaptcha here?
		{
			require_once('recaptchalib.php');
			$privatekey='6Ld8cskSAAAAAOCdGESm17P58trbl2PI-O5-BIry';
			$re_chall=isset($_POST['recaptcha_challenge_field'])?$_POST['recaptcha_challenge_field']:'';
			$re_resp=isset($_POST['recaptcha_response_field'])?$_POST['recaptcha_response_field']:'';
			$resp=recaptcha_check_answer($privatekey,$_SERVER['REMOTE_ADDR'],$re_chall,$re_resp);
			if($ccheck) //pre-check, if valid - set session
			{
				if($resp->is_valid)
					Session::setVar(self::$f->cap_id,md5('verified')); //indicator for the actual check
				return ($resp->is_valid);
			}
			else
			{//actual check
				if(Session::isSessionSet(self::$f->cap_id)&&Session::getVarStr(self::$f->cap_id)==md5('verified'))
				{ //looks like it was already validated in the pre-check.
					Session::unsetVar(self::$f->cap_id);   //we don't neet this anymore
					return true;
				}
				else
					return $resp->is_valid;  //no pre check (blog comment post, etc) just return the check
			}
		}
		else
		{
			$captcha=Session::getVar(self::$f->cap_id);
			if($captcha==''||$captcha==NULL)
				$check_failed=true;//exit('0|This is illegal operation.');
			else
				$check_failed=(!isset($_POST[$inputName])||(md5(strtoupper($_POST[$inputName]))!=$captcha));
			return !$check_failed;
		}
	}

}

class Password extends FuncHolder
{

	public static function checkStrenght($pwd,$thispage_id,$get_arr_only=false,$is_admin=false)
	{
		$lang=CA::getMyprofileLabels($thispage_id);
		$str=array(
			'short'=>$lang['short pwd'],//1
			'weak'=>$lang['weak'],//2
			'average'=>$lang['average'],//3
			'good'=>$lang['good'],//4
			'strong'=>$lang['strong'],//5
			'forbidden'=>$lang['forbidden']
		);
		if($get_arr_only)
			return $str; //added this to define the labels at one place only
//only longer than 8 chars
		$weak_passwords=array('firebird','password','12345678','steelers','mountain','computer','baseball','xxxxxxxx','football','qwertyui','jennifer','danielle','sunshine','starwars','whatever','nicholas','swimming','trustno1','midnight','princess','startrek','mercedes','superman','bigdaddy','maverick','einstein','dolphins','hardcore','redwings','cocacola','michelle','victoria','corvette','butthead','marlboro','srinivas','internet','redskins','11111111','access14','rush2112','scorpion','iloveyou','samantha','mistress');
		$ret_num_min=3;
		if(in_array($pwd,$weak_passwords))
		{
			$msg=$str['forbidden'];
			$ret_num=2;
		}  //block weak passwords
		else
		{
			$msg='';
			$ret_num=0;
			if(preg_match('/^.{1,7}$/',$pwd))
			{
				$msg=$str['short'];
				$ret_num=1;
			}
			elseif(preg_match('/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/',$pwd))
			{
				$msg=$str['strong'];
				$ret_num=5;
			}
			elseif(preg_match('/^.{12,}$/',$pwd))
			{
				$msg=$str['strong'];
				$ret_num=5;
			}
			elseif(preg_match('/(?=^.{8,}$)(?=.*\d)(?![.\n])(?=.*[a-zA-Z]).*$/',$pwd))
			{
				$msg=$str['good'];
				$ret_num=4;
			}
			elseif(preg_match('/^[^0-9]{8,11}$/',$pwd))
			{
				$msg=$str['average'];
				$ret_num=3;
			}
			elseif(preg_match('/^[0-9]{8,}$/',$pwd))
			{
				$msg=$str['weak'];
				$ret_num=2;
			}
			else
				$msg="That's not a password!";
		}
		$is_pass_ok=$is_admin?true:($ret_num>=$ret_num_min);

		return array('num'=>$ret_num,'msg'=>$msg,'pass_is_ok'=>$is_pass_ok);
	}

	public static function showMeter($pass_levels,$pos='right',$mt='6',$id='')
	{
		$outDivStart='<div class="out_pass_div'.$id.'" style="height:4px;position:relative;">';
		$innDivStart='<div class="inn_pass_div'.$id.'" style="height:4px;margin-top:'.$mt.'px">';
		$outDivEnd=$innDivEnd='</div>';
		$txtSpan='<span id="pwdptext_%s'.$id.'" style="display:none;line-height:4px;position:absolute;top:0;'.$pos.':0;" class="pass_progress_text'.$id.' rvts8 field_label">%s</span>';

		$output=$outDivStart.$innDivStart.$innDivEnd;
		foreach($pass_levels as $pk=>$pv)
			$output.=sprintf($txtSpan,$pk,$pv);

		return $output.$outDivEnd;
	}

}

class Cookie extends FuncHolder
{

	public static function entryID($page_id,$prefix)
	{
		return $prefix.self::$f->proj_id.$page_id;
	}

	public static function entryIsCookie($entry_id,$page_id,$prefix)
	{
		$cookie_id=self::entryID($page_id,$prefix);
		$name=$prefix.$page_id.$entry_id;
		if(isset($_COOKIE[$name]))
		{
			self::setEntryCookie($name);
			setcookie($name,'',time()-3600);
		}
		if(isset($_COOKIE[$cookie_id]))
			return (strpos($_COOKIE[$cookie_id],$entry_id.'_')!==false);
		else
			return false;
	}

	public static function setEntryCookie($entry_id,$page_id,$prefix)
	{
		$timestamp=time();
		$expire_timestamp=mktime(23,59,59,date('n',$timestamp),date('j',$timestamp),2037);
		$cookie_id=self::entryID($page_id,$prefix);
		$cookie=(isset($_COOKIE[$cookie_id])?$_COOKIE[$cookie_id]:'');
		$cookie=substr($entry_id.'_'.$cookie,0,3999);
		setcookie($cookie_id,$cookie,$expire_timestamp);
		$_COOKIE[$cookie_id]=$cookie;
	}

	public static function isAdmin()
	{
		global $user;
		return $user->isLogged(self::$f->admin_cookieid);
	}

	public static function getAdmin()
	{
		return Session::getVarStr(self::$f->admin_cookieid);
	}

	public static function setAdmin($c)
	{
		Session::setVar(self::$f->admin_cookieid,$c);
	}

	public static function setLongtimeLogin($user,$expire_time)
	{
	}

	public static function checkLoginCookies()
	{
		$is_set_common_data=(isset($_COOKIE[self::$f->login_cb_str.'hash'.self::$f->proj_id])&&isset($_COOKIE[self::$f->login_cb_str.'t'.self::$f->proj_id])&&isset($_COOKIE[self::$f->login_cb_str.'usr'.self::$f->proj_id]));

		if($is_set_common_data)
		{
			$ret=array(
				'username'=>$_COOKIE[self::$f->login_cb_str.'usr'.self::$f->proj_id],
				'time'=>$_COOKIE[self::$f->login_cb_str.'t'.self::$f->proj_id],
				'hash'=>$_COOKIE[self::$f->login_cb_str.'hash'.self::$f->proj_id]);
			if(isset($_COOKIE[self::$f->login_cb_str.'exh'.self::$f->proj_id]))
				$ret['extrahash']=$_COOKIE[self::$f->login_cb_str.'exh'.self::$f->proj_id];
			return $ret;
		}
		else
			return false;
	}

	//login cookiebased functions
	public static function makeCookieHash($user,$t_hash)
	{
		return sha1(self::$f->proj_id.$t_hash.$user['username'].$user['password'].$user['status']);
	}

	public static function makeDBHash($username,$cookie_hash)
	{
		return sha1(self::$f->proj_id.'for'.$username.'with'.$cookie_hash);
	}

	public static function getLangCookie()
	{
		global $user;
		$lang='';
		Session::intStart('private');
		if($user->userCookie())
			$logged_user=$user->getUserCookie();
		elseif(self::isAdmin())
			$logged_admin=self::getAdmin();
		if(isset($logged_user)&&isset($_COOKIE[$logged_user.'_lang']))
			$lang=Formatter::strToUpper(Formatter::stripTags($_COOKIE[$logged_user.'_lang']));
		elseif(isset($logged_admin)&&isset($_COOKIE['ca_lang']))
			$lang=Formatter::strToUpper(Formatter::stripTags($_COOKIE['ca_lang']));
		return $lang;
	}

}

class Date extends FuncHolder
{

	public static function dp($month_name,$ts)
	{
		$mon=date('n',$ts);
		$mon_name=$month_name[$mon-1];
		return $mon_name.date(' j, Y',$ts);
	}

	public function get_date_format($pageLang,$mode='long')
	{
		$lid=array_search($pageLang,self::$f->inter_languages_a);
		$params=str_replace('DD, d MM, yy','dd mmmm, yyyy',self::$f->date_format_a[$lid]);
		if($mode=='long')
				$params.=(self::$f->time_format_a[$lid]==12)?' h:i A':' H:i';
		return $params;
	}

	public function format_date($timestamp,$pageLang,$month_name,$day_name,$mode='long',$params='')
	{
		if($params=='')
			$params=Date::get_date_format($pageLang,$mode);

		$res=$timestamp<0?'   ':self::format($timestamp,$params,$month_name,$day_name,$mode);
		return $res;
	}

	public static function format($timestamp,$params,$month_names,$day_names,$mode,$use_tzone=true) # mode --> short, long
	{
		$res='';
		$ts=($use_tzone)?self::tzone($timestamp):$timestamp;

		if(!empty($params))
		{
			$params=str_replace(array('dddd','ddd','DDDD','DDD','dd','d','mmmm','mmm','MMMM','MMM','mm','m','yyyy','yy','hh','nn','ss'),array('XX3','XX4','XX32','XX42','XX5','j','XX2','XX1','XX22','XX12','XX6','n','Y','y','H','i','s'),$params);

			$res=str_replace('XX5','d',$params);
			$res=str_replace('XX6','m',$res);
			$res=date($res,$ts);
			$res=str_replace('XX12',Formatter::strToUpper(Formatter::mySubstr($month_names[date('n',$ts)-1],0,3)),$res);
			$res=str_replace('XX22',Formatter::strToUpper($month_names[date('n',$ts)-1]),$res);
			$res=str_replace('XX42',Formatter::strToUpper(Formatter::mySubstr($day_names[date('w',$ts)],0,3)),$res);
			$res=str_replace('XX32',Formatter::strToUpper($day_names[date('w',$ts)]),$res);
			$res=str_replace('XX1',Formatter::mySubstr($month_names[date('n',$ts)-1],0,3),$res);
			$res=str_replace('XX2',$month_names[date('n',$ts)-1],$res);
			$res=str_replace('XX4',Formatter::mySubstr($day_names[date('w',$ts)],0,3),$res);
			$res=str_replace('XX3',$day_names[date('w',$ts)],$res);
		}
		else
			$res=($mode=='short')?$month_names[date('n',$ts)-1].date(', Y',$ts):$month_names[date('n',$ts)-1].date(' d, Y',$ts);
		return $res;
	}

	public static function formatTime($timestamp,$time_format,$mode='short') # mode --> short, long
	{
		$ts=self::tzone($timestamp);
		$res=($mode=='short')?($time_format==12?date(' g:i A',$ts):date(' G:i',$ts)):($time_format==12?date(' d, Y g:i A',$ts):date(' d, Y G:i',$ts));
		return $res;
	}

	public static function tzoneNow()
	{
		$dt=date("Y-m-d_H:i:s",self::tzone(time()));
		return $dt;
	}

	public static function tzone($date,$reversed=false)
	{
		if(self::$f->tzone_offset==-10000)
		{
			self::$f->tzone_offset=intval(File::readTaggedData(self::$f->ca_settings_fname,'tzoneoffset'));
		}

		$fixed_date=$date;
		if(self::$f->tzone_offset!=0)
		{
			if($reversed)
				$fixed_date=$date-self::$f->tzone_offset*60*60;
			else
				$fixed_date=$date+self::$f->tzone_offset*60*60;
		}

		return $fixed_date;
	}

	public static function daysInFeb($year)
	{
		if($year<0)
			$year++;
		$year+=4800;
		if(($year%4)==0)
		{
			if(($year%100)==0)
			{
				if(($year%400)==0)
					return(29);
				else
					return(28);
			}
			else
				return(29);
		}
		else
			return(28);
	}

	public static function daysInMonth($month,$year)
	{
		if($month==0) //probably curr month is Jan and Dec of last year is checked
		{
			$month=12;
			$year -= 1;
		}
		if($month==2)
			return self::daysInFeb($year);
		else
		{
			if($month==1||$month==3||$month==5||$month==7||$month==8||$month==10||$month==12)
				return(31);
			else
				return(30);
		}
	}

	public static function pareseInputDate($fname,$time_format,$month_name)
	{
		if(isset($_POST[$fname]))
		{
			$postFname=trim($_POST[$fname]);
			$gethm=isset($_POST[$fname.'_hour']);
			$postFnameHour=$gethm?trim($_POST[$fname.'_hour']):'0';
			$postFnameMin=$gethm?trim($_POST[$fname.'_min']):'0';
			$postFnameAmPm=($gethm&&$time_format==12&&isset($_POST[$fname.'_ampm']))?trim($_POST[$fname.'_ampm']):'';
			list($tt,$yy)=explode(',',$postFname);
			list($mm,$dd)=explode(' ',$tt);
			$m=array_search($mm,$month_name);
			$start_hour=intval($time_format==12?($postFnameAmPm=='AM'?$postFnameHour:($postFnameHour+12)):($postFnameHour));
			$date=mktime($start_hour,intval($postFnameMin),0,($m+1),intval($dd),intval($yy));
			$date=self::tzone($date,true);
		}
		else
			$date=self::tzone(time());
		return $date;
	}

	public static function buildMysqlTime($ts='',$from_ico=false)
	{
		if($from_ico)
			return str_replace(array('T','+00:00'),array(' ',''),$ts);
		elseif($ts!='')
			return date('Y-m-d H:i:s',$ts);
		else
			return date('Y-m-d H:i:s');
	}

	public static function isCurrentDay($day,$mon,$year) //  current day check
	{
		$current_date=getdate(self::tzone(time()));
		$currday=$current_date['mday'];
		$currmon=$current_date['mon'];
		$curryear=$current_date['year'];
		if($day==$currday&&$mon==$currmon&&$year==$curryear)
			return true;
		else
			return false;
	}

	public static function microtimeFloat()
	{
		list($usec,$sec)=explode(" ",microtime());
		return ((float)$usec+(float)$sec);
	}

}

class Mobile extends FuncHolder
{
//taken from Mobile_detect.php
	protected static $tabletDevices = array(
		'iPad'              => 'iPad|iPad.*Mobile',
		'NexusTablet'       => '^.*Android.*Nexus(((?:(?!Mobile))|(?:(\s(7|10).+))).)*$',
		'SamsungTablet'     => 'SAMSUNG.*Tablet|Galaxy.*Tab|SC-01C|GT-P1000|GT-P1003|GT-P1010|GT-P3105|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3108|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P7501|GT-N5100|GT-N5105|GT-N5110|SHV-E140K|SHV-E140L|SHV-E140S|SHV-E150S|SHV-E230K|SHV-E230L|SHV-E230S|SHW-M180K|SHW-M180L|SHW-M180S|SHW-M180W|SHW-M300W|SHW-M305W|SHW-M380K|SHW-M380S|SHW-M380W|SHW-M430W|SHW-M480K|SHW-M480S|SHW-M480W|SHW-M485W|SHW-M486W|SHW-M500W|GT-I9228|SCH-P739|SCH-I925|GT-I9200|GT-I9205|GT-P5200|GT-P5210|GT-P5210X|SM-T311|SM-T310|SM-T310X|SM-T210|SM-T210R|SM-T211|SM-P600|SM-P601|SM-P605|SM-P900|SM-P901|SM-T217|SM-T217A|SM-T217S|SM-P6000|SM-T3100|SGH-I467|XE500|SM-T110|GT-P5220|GT-I9200X|GT-N5110X|GT-N5120|SM-P905|SM-T111|SM-T2105|SM-T315|SM-T320|SM-T320X|SM-T321|SM-T520|SM-T525|SM-T530NU|SM-T230NU|SM-T330NU|SM-T900|XE500T1C|SM-P605V|SM-P905V|SM-P600X|SM-P900X|SM-T210X|SM-T230|SM-T230X|SM-T325|GT-P7503|SM-T531|SM-T330|SM-T530|SM-T705C|SM-T535|SM-T331|SM-T800',
		'Kindle'            => 'Kindle|Silk.*Accelerated|Android.*\b(KFTT|KFOTE)\b',
		'SurfaceTablet'     => 'Windows NT [0-9.]+; ARM;',
		'AsusTablet'        => '^.*PadFone((?!Mobile).)*$|Transformer|TF101|TF101G|TF300T|TF300TG|TF300TL|TF700T|TF700KL|TF701T|TF810C|ME171|ME301T|ME302C|ME371MG|ME370T|ME372MG|ME172V|ME173X|ME400C|Slider SL101|\bK00F\b|TX201LA',
		'BlackBerryTablet'  => 'PlayBook|RIM Tablet',
		'HTCtablet'         => 'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200',
		'MotorolaTablet'    => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
		'NookTablet'        => 'Android.*Nook|NookColor|nook browser|BNRV200|BNRV200A|BNTV250|BNTV250A|LogicPD Zoom2',
		'AcerTablet'        => 'Android.*; \b(A100|A101|A110|A200|A210|A211|A500|A501|A510|A511|A700|A701|W500|W500P|W501|W501P|W510|W511|W700|G100|G100W|B1-A71)\b',
		'ToshibaTablet'     => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)|TOSHIBA.*FOLIO',
		'LGTablet'          => '\bL-06C|LG-V900|LG-V909\b',
		'FujitsuTablet'     => 'Android.*\b(F-01D|F-05E|F-10D|M532|Q572)\b',
		'PrestigioTablet'   => 'PMP3170B|PMP3270B|PMP3470B|PMP7170B|PMP3370B|PMP3570C|PMP5870C|PMP3670B|PMP5570C|PMP5770D|PMP3970B|PMP3870C|PMP5580C|PMP5880D|PMP5780D|PMP5588C|PMP7280C|PMP7280|PMP7880D|PMP5597D|PMP5597|PMP7100D|PER3464|PER3274|PER3574|PER3884|PER5274|PER5474|PMP5097CPRO|PMP5097|PMP7380D',
		'LenovoTablet'      => 'IdeaTab|S2110|S6000|K3011|A3000|A1000|A2107|A2109|A1107',
		'YarvikTablet'      => 'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)',
		'MedionTablet'      => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
		'ArnovaTablet'      => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT',
		'IRUTablet'         => 'M702pro',
		'MegafonTablet'     => 'MegaFon V9|ZTE V9',
		'AllViewTablet'           => 'Allview.*(Viva|Alldro|City|Speed|All TV|Frenzy|Quasar|Shine|TX1|AX1|AX2)',
		'ArchosTablet'      => 'Android.*ARCHOS|\b101G9\b|\b80G9\b',
		'AinolTablet'       => 'NOVO7|Novo7Aurora|Novo7Basic|NOVO7PALADIN',
		'SonyTablet'        => 'Sony.*Tablet|Xperia Tablet|Sony Tablet S|SO-03E|SGPT12|SGPT121|SGPT122|SGPT123|SGPT111|SGPT112|SGPT113|SGPT211|SGPT213|SGP311|SGP312|SGP321|EBRD1101|EBRD1102|EBRD1201',
		'CubeTablet'        => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)|CUBE U8GT',
		'CobyTablet'        => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7015|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8024|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
		'MIDTablet'         => 'M9701|M9000|M9100|M806|M1052|M806|T703|MID701|MID713|MID710|MID727|MID760|MID830|MID728|MID933|MID125|MID810|MID732|MID120|MID930|MID800|MID731|MID900|MID100|MID820|MID735|MID980|MID130|MID833|MID737|MID960|MID135|MID860|MID736|MID140|MID930|MID835|MID733',
		'SMiTTablet'        => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
		'RockChipTablet'    => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
		'TelstraTablet'     => 'T-Hub2',
		'FlyTablet'         => 'IQ310|Fly Vision',
		'bqTablet'          => 'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)',
		'HuaweiTablet'      => 'MediaPad|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
		'NecTablet'         => '\bN-06D|\bN-08D',
		'PantechTablet'     => 'Pantech.*P4100',
		'BronchoTablet'     => 'Broncho.*(N701|N708|N802|a710)',
		'VersusTablet'      => 'TOUCHPAD.*[78910]|\bTOUCHTAB\b',
		'ZyncTablet'        => 'z1000|Z99 2G|z99|z930|z999|z990|z909|Z919|z900',
		'PositivoTablet'    => 'TB07STA|TB10STA|TB07FTA|TB10FTA',
		'NabiTablet'        => 'Android.*\bNabi',
		'KoboTablet'        => 'Kobo Touch|\bK080\b|\bVox\b Build|\bArc\b Build',
		'DanewTablet'       => 'DSlide.*\b(700|701R|702|703R|704|802|970|971|972|973|974|1010|1012)\b',
		'TexetTablet'       => 'NaviPad|TB-772A|TM-7045|TM-7055|TM-9750|TM-7016|TM-7024|TM-7026|TM-7041|TM-7043|TM-7047|TM-8041|TM-9741|TM-9747|TM-9748|TM-9751|TM-7022|TM-7021|TM-7020|TM-7011|TM-7010|TM-7023|TM-7025|TM-7037W|TM-7038W|TM-7027W|TM-9720|TM-9725|TM-9737W|TM-1020|TM-9738W|TM-9740|TM-9743W|TB-807A|TB-771A|TB-727A|TB-725A|TB-719A|TB-823A|TB-805A|TB-723A|TB-715A|TB-707A|TB-705A|TB-709A|TB-711A|TB-890HD|TB-880HD|TB-790HD|TB-780HD|TB-770HD|TB-721HD|TB-710HD|TB-434HD|TB-860HD|TB-840HD|TB-760HD|TB-750HD|TB-740HD|TB-730HD|TB-722HD|TB-720HD|TB-700HD|TB-500HD|TB-470HD|TB-431HD|TB-430HD|TB-506|TB-504|TB-446|TB-436|TB-416|TB-146SE|TB-126SE',
		'PlaystationTablet' => 'Playstation.*(Portable|Vita)',
		'GalapadTablet'     => 'Android.*\bG1\b',
		'MicromaxTablet'    => 'Funbook|Micromax.*\b(P250|P560|P360|P362|P600|P300|P350|P500|P275)\b',
		'KarbonnTablet'     => 'Android.*\b(A39|A37|A34|ST8|ST10|ST7|Smart Tab3|Smart Tab2)\b',
		'GUTablet'          => 'TX-A1301|TX-M9002|Q702',
		'GenericTablet'     => 'Android.*\b97D\b|Tablet(?!.*PC)|ViewPad7|BNTV250A|MID-WCDMA|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|hp-tablet|rk30sdk',
	);
//end

	public static function isTablet($userAgent = null)
	{
		foreach(self::$tabletDevices as $k=>$regex)
		{
			$regex=str_replace('/','\/',$regex);
			if((bool) preg_match('/'.$regex.'/is',$userAgent ) ) return $k;
		}
		return false;
	}

	public static function detect($mode)
	{
		$res=false;
		$fvc=false;

		if(isset($_REQUEST['fullview']))
		{
			setcookie('use_fullview','1',0,'/');
			$fvc=true;
		}
		elseif(isset($_COOKIE['use_fullview']))
			$fvc=true;
		if(isset($_REQUEST['mobileview']))
		{
			setcookie("use_fullview","",time()-3600,'/');
			$fvc=false;
		}
		if(!$fvc&&$mode!='0'&&isset($_SERVER['HTTP_USER_AGENT']))
		{
			$us_agent=$_SERVER['HTTP_USER_AGENT'];
			/*if($mode=='1')
			{
				if(strpos($us_agent,'iPhone')!==false||strpos($us_agent,'iPod')!==false)
					$res=true;
			}
			else */
			if($mode=='2' || $mode=='1')			
			{
				if(self::isTablet($us_agent)!==false)	$res=false;
				elseif(preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$us_agent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($us_agent,0,4)))
					$res=true;
			}
		}
		return $res;
	}

}

//detects and returns the required data
class Detector extends FuncHolder
{

	public static function getMime($ext,$default='audio/mpeg3')
	{
		if(strpos($ext,'tif')!==false)
			$mime='image/tiff';
		elseif(strpos($ext,'png')!==false)
			$mime='image/png';
		elseif(strpos($ext,'gif')!==false)
			$mime='image/gif';
		elseif(strpos($ext,'jp')!==false)
			$mime='image/jpeg';
		elseif(strpos($ext,'pdf')!==false)
			$mime='application/pdf';
		elseif(strpos($ext,'swf')!==false)
			$mime='application/x-shockwave-flash';
		elseif(strpos($ext,'doc')!==false)
			$mime='application/msword';
		elseif(strpos($ext,'wav')!==false)
			$mime='audio/wav';
		elseif(strpos($ext,'avi')!==false)
			$mime='video/avi';
		elseif(strpos($ext,'mp4')!==false)
			$mime='video/mp4';
		else
			$mime=$default;
		return $mime;
	}

	public static function defineOS($agent)
	{
		$os=array(
			'1'=>'Windows 95|Win95|Windows_95',
			'2'=>'Windows 98|Win98',
			//WIN NT, moved to bottom
			'4'=>'Windows NT 5.0|Windows 2000',
			'5'=>'Windows NT 5.1|Windows XP',
			'6'=>'Windows NT 5.2',
			'7'=>'Windows NT 6.0',
			'8'=>'Linux|X11|Ubuntu|Debian|FreeBSD',
			'9'=>'Mac_PowerPC|Macintosh',
			//"Windows" in the self::$f->os array, not sure why removed
			'11'=>'Windows NT 6.1',
			'12'=>'iPhone|Ipod|Ipad',
			'13'=>'nuhk|Googlebot|Yammybot|Openbot|Slurp\/cat|msnbot|ia_archiver',
			'14'=>'Android',
			'15'=>'Windows NT 6.2',
			'16'=>'BlackBerry|RIM Tablet OS',
			'3'=>'Windows NT 4.0|WinNT4.0|WinNT|Windows NT'
		); //WIN NT (3) must be last one checked (among the windows OSes)
		foreach($os as $k=> $v)
		{
			if(preg_match('/'.$v.'/i',$agent))
				return self::$f->os[intval($k)];
		}
		return 'Unknown';
	}

	public static function readUserAgent($agent,$host)
	{
		$result=array();
		$p=array_search(self::defineOS($agent),self::$f->os);
		$b='0'; //Unknown
		if(strpos($agent,'MSIE')!==false)
		{
			if(strpos($agent,'MSIE 10')!==false)
				$b='30';
			elseif(strpos($agent,'MSIE 9')!==false)
				$b='20';
			elseif(strpos($agent,'MSIE 8')!==false)
				$b='19';
			elseif(strpos($agent,'MSIE 7')!==false&&strpos($agent,'Trident/4.0')!==false)
				$b='19';
			elseif(strpos($agent,'MSIE 7')!==false)
				$b='10';
			elseif(strpos($agent,'MSIE 6')!==false)
				$b='9';
			else
				$b='1';
		}
		elseif(strpos($agent,'Firefox')!==false)
			$b='3';
		elseif(strpos($agent,'Opera')!==false)
			$b='2';
		elseif(strpos($agent,'Chrome')!==false)
			$b='18';
		elseif(strpos($agent,'Mercury')!==false)
			$b='31';
		elseif(strpos($agent,'Safari')!==false)
			$b='6';
		elseif((strpos($agent,'Konqueror')!==false)||(strpos($agent,'KHTML')!==false))
			$b='7';
		elseif((strpos($host,'googlebot.com')!==false))
			$b='4';

		$result['platform']=$p;
		$result['browser']=$b;
		return $result;
	}

	public static function getRemoteHost()
	{
		$host='unknown';
		if(isset($_SERVER['REMOTE_HOST']))
			$host=trim($_SERVER['REMOTE_HOST']);
		elseif(isset($_SERVER['REMOTE_ADDR']))
			$host=gethostbyaddr($_SERVER['REMOTE_ADDR']);
		return $host;
	}

	public static function getIP()
	{
		if(isset($_SERVER["HTTP_CLIENT_IP"]))
			return $_SERVER["HTTP_CLIENT_IP"];
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
		if(isset($_SERVER["HTTP_X_FORWARDED"]))
			return $_SERVER["HTTP_X_FORWARDED"];
		if(isset($_SERVER["HTTP_FORWARDED_FOR"]))
			return $_SERVER["HTTP_FORWARDED_FOR"];
		if(isset($_SERVER["HTTP_FORWARDED"]))
			return $_SERVER["HTTP_FORWARDED"];
		if(isset($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];
		if(isset($_SERVER["HTTP_PC_REMOTE_ADDR"]))
			return $_SERVER["HTTP_PC_REMOTE_ADDR"];
		return("unknown");
	}

	public static function defineSourcePage($root='../',$lang='',$use_mobile=false,$dont_use_php=false)
	{
		$result='';
		self::$f->template_source_f=$root.self::$f->template_source;
		if($use_mobile)
			self::$f->template_source_f=strpos(self::$f->template_source_f,'../')!==false?Formatter::strLReplace('/','/i_',self::$f->template_source_f):'i_'.self::$f->template_source_f;
		if(file_exists(self::$f->template_source_f))
			$result=self::$f->template_source_f;
		elseif(file_exists($root.self::$f->sitemap_fname)&&filesize($root.self::$f->sitemap_fname)>0)
		{
			if(isset($_REQUEST['id']))
				$id=intval($_REQUEST['id']);
			$fp=fopen($root.self::$f->sitemap_fname,'r');
			if(isset($id)) //getting current page
			{
				while(($data=fgetcsv($fp,self::$f->max_chars,'|'))&&($result==''))
				{
					if(isset($data[10])&&($data[10]=='<id>'.$id))
						$result=self::checkSourcePage($data,$id,$use_mobile);
				}
				if(strpos($result,'.php')!==false && $dont_use_php) $result='';
			}
			if($result=='') //getting any page
			{
				fseek($fp,0);
				while(($data=fgetcsv($fp,self::$f->max_chars,'|'))&&($result==''))
				{
					if($lang!='')
					{
						if(isset($data[22])&&($data[22]==$lang))
							$result=self::checkSourcePage($data,str_replace('<id>','',$data[10]),$use_mobile);
					}
					else if(isset($data[10]))
						$result=self::checkSourcePage($data,str_replace('<id>','',$data[10]),$use_mobile);
					if(strpos($result,'.php')!==false && $dont_use_php) $result='';
					elseif(strpos($result,'?')!==false) $result='';
					if($result!='') break;
				}
			}
			if($result=='') //getting any page
			{
				fseek($fp,0);
				while(($data=fgetcsv($fp,self::$f->max_chars,'|'))&&($result==''))
				{
					if(isset($data[10]))
						$result=self::checkSourcePage($data,str_replace('<id>','',$data[10]),$use_mobile);
					if($result!='') break;
				}
			}

//still nothing=> no html page in the project=> we use 1st php page as template
			if($result=='')
			{
				fseek($fp,0);
				while(($data=fgetcsv($fp,self::$f->max_chars,'|'))&&($result==''))
				{
					if(isset($data[10]))
						if(strpos($data[1],'.php')!==false) //just for sure the page is php
						{
							$result=$data[1];
							break;
						}
				}
			}
			fclose($fp);
		}
		return $result;
	}

	public static function checkSourcePage($data,$id,$use_mobile=false,$check_in_normal=false)
	{
		$fname='';
		$used_for_mob_search=isset($_REQUEST['mobile_search'])&&$_REQUEST['mobile_search']==1;
		if(strpos($data[1],'http:')===false&&strpos($data[1],'https:')===false)
		{
			if(in_array(intval($data[4]),array(CALENDAR_PAGE,BLOG_PAGE,PHOTOBLOG_PAGE,PODCAST_PAGE,GUESTBOOK_PAGE,OEP_PAGE,SURVEY_PAGE,BLOG_VIEW,PHOTOBLOG_VIEW))) //Special pages
			{
				self::$f->dir=(strpos($data[1],'../')===false)?'':'../'.Formatter::GFS($data[1],'../','/').'/';
				$fname=self::$f->dir.$id.(Validator::checkProtection($data)>1?'.php':'.html');
			}
			elseif(in_array($data[4],array(SHOP_PAGE,CATALOG_PAGE,REQUEST_PAGE)))//shop/lister/request
			{
				self::$f->dir=(strpos($data[1],'../')===false)?'':'../'.Formatter::GFS($data[1],'../','/').'/';
				$fname=self::$f->dir.($data[4]==REQUEST_PAGE?($id+1):$id).'.html';
			}
			elseif(Validator::checkProtection($data)==1&&($data[4]=='0'||$data[4]=='1'||$data[4]>199) /* && strpos($data[1],'.html')!==false */)
				$fname=$data[1];  //normal page
		}
		$check_mobile=Mobile::detect($data[24])==true;
		if($check_in_normal)
			$check_mobile=$data[24]!='0'; //check only whether page has mobile or not

		if($use_mobile&&($check_mobile||$used_for_mob_search))
		{
			if(strpos($fname,'/')===false)
				$fname='i_'.$fname;
			else
			{
				$temp_name=substr($fname,strrpos($fname,'/')+1);
				$fname=str_replace($temp_name,'i_'.$temp_name,$fname);
			}
			self::$f->mobile_detected=true;
		}
		return $fname;
	}

	public static function fileExt($src)
	{
		$ext_pos=strrpos($src,".");
		$ext=substr($src,$ext_pos);
		return $ext;
	}

}

//not quite sure why I added this. Maybe will move some functions here
class Login extends FuncHolder
{

}

class Session extends FuncHolder
{

	public static function isSessionSet($Var)
	{
		return isset($_SESSION[$Var]);
	}

	//using null isntead of ''. Formatter::stripTags(null) is string "", so it should not be a problem
	public static function getVar($Var)
	{
		return (isset($_SESSION[$Var])?$_SESSION[$Var]:NULL);
	}

	public static function getVarStr($var)
	{
		return Formatter::stripTags(self::getVar($var));
	}

	public static function setVar($Var,$varValue)
	{
		$_SESSION[$Var]=$varValue;
	}

	public static function setVarArr($Var,$varArrId,$arr)
	{
		$_SESSION[$Var][$varArrId]=$arr;
	}

	public static function unsetVar($Var)
	{
		unset($_SESSION[$Var]);
	}

	public static function unsetSession()
	{
		$_SESSION=array();
		if(isset($_COOKIE[session_name()]))
			setcookie(session_name(),'',time()+1,'/');

		session_destroy();
	}

	//^^^^^^^^^^^^cookiebased session functions^^^^^^^^^^^^
	public static function f_sess_open()
	{
		return true;
	}

	public static function f_sess_close()
	{
		return true;
	}

	//NORMAL SESSION FUNCTIONS
	public static function intStart($flag='',$regen_id=false,$sess_id=false)
	{
		$curr_sess_id=session_id();
		if(isset($_SESSION)&&($sess_id===false||$sess_id==$curr_sess_id))
			return false; //don't do anything if session is already started

		$ssp='';
		if(($ssp!='')&&(strpos($ssp,'%SESSIONS_')===false))
			session_save_path($ssp);
		session_name('PHPSESSID'.self::$f->proj_id);

		if($sess_id!==false&&$sess_id!=$curr_sess_id)
		{
			session_start();
			session_destroy();
			session_id($sess_id);
			session_start();
			if(self::isSessionSet('HTTP_USER_AGENT'))
				self::unsetVar('HTTP_USER_AGENT');
		}
		else
			session_start();

		if($flag=='private')
			header("Cache-control: private");
		if($regen_id&&$sess_id===false)
			self::regenerateID(); //don't allow regen id if specific sess id is set
	}

	public static function regenerateID()
	{
		if(function_exists('session_regenerate_id'))
			session_regenerate_id();
		}

}

class Search extends FuncHolder
{
	public static function catBox($action,$lang_l,$cat_id='',&$js)
	{

		$output='<div id="category_search_ct" style="display:inline;position:relative;padding:0 0 12px 2px;">';
		$output.='<form name="category_search" action="'.$action.'" method="post" onsubmit="return document.category_search.q.value!=\'\'">';
		$js.='$(document).ready(function(){ $(".cat_chb").click(function(){$(".allcat_chb").attr("checked",false);});$(".allcat_chb").click(function(){$(".cat_chb").attr("checked",false);});'.
			'$("#search_edit").focus(function() {$("#scb").fadeIn("fast");}).click(function() {$("#scb").fadeIn("fast");});$("#category_search_ct").mouseleave(function(){$("#scb").fadeOut("fast");});});';
		$output.=' <input class="input1" id="search_edit" type="text" name="q" autocomplete="off" value="" >
			 <input class="input1" id="search_btn" type="submit" name="search" value="'.$lang_l['search'].'" >';
		$output.='<div class="input1" id="scb" style="display:none;text-align:left;position:absolute;z-index:100;min-width:180px;top:23px;left:2px;padding:4px;box-shadow:0 0 4px #606060;background:white;">%CAT_SEARCH%</div></form></div>';
		return $output;
	}

}

class CA extends FuncHolder
{
	public static function getAdminScreenClass()
	{
		return 'a_body'.(self::getCaMiniCookie()?' small':'');
	}

	public static function getCaMiniCookie()
	{
		return isset($_COOKIE['ca_folded']) && $_COOKIE['ca_folded']=='1';
	}

	public static function setCaMiniCookie($folded)
	{
  	$folded?setcookie('ca_folded','1',0,'/'):setcookie('ca_folded', '', time()-1000);
	}

	/* ------------------ central admin functions ------------------- */

	public static function getSitemap($root_path,$incl_cats=false,$return_assoc=false,$only_for_cat='')
	{
		$result=array();

		$filename=(strpos($root_path,'sitemap.php')!==false)?$root_path:$root_path.self::$f->sitemap_fname;
		if(file_exists($filename))
		{
			$fsize=filesize($filename);
			if($fsize>0)
			{
				$fp=fopen($filename,'r');
				$content=fread($fp,$fsize);
				fclose($fp);
				$lines_a=explode("\n",$content);
				$count=count($lines_a);
				for($i=1; $i<$count; $i++)
				{
					if(strpos($lines_a[$i],'<?php echo "hi"; exit; /*')===false&&strpos($lines_a[$i],'*/ ?>')===false)
					{
						if($incl_cats||strpos($lines_a[$i],'<id>')!==false)
						{
							$line_arr=explode("|",trim($lines_a[$i]));
							if(strpos($line_arr[0],'#')!==false&&strpos($line_arr[0],'#')==0)
								$line_arr[0]=str_replace('#','',$line_arr[0]);
							if($return_assoc)
							{
								$id=str_replace('<id>','',$line_arr[10]);
								$result["$id"]=$line_arr;
							}
							else
								$result[]=$line_arr;
						}
					}
				}
			}
		}
		return $result;
	}

	public static function getPageParams($id,$root_path='../',$use_next_page=false)
	{
		$forms=array_merge(self::$f->subminiforms,self::$f->subminiforms_news);
		if(array_key_exists($id,$forms)||($id==0&&isset($_GET['pageid'])&&array_key_exists($_GET['pageid'],$forms)))
			$id=$forms[$id];

		if($id==0)
			return '';
		if(isset(self::$f->page_params[$id]))
			$result=self::$f->page_params[$id];
		else
		{
			$result='';
			$all_pages=self::getSitemap($root_path);
			foreach($all_pages as $k=> $v)
			{
				if($v[10]=='<id>'.$id)
				{
					$result=$v;
					break;
				}
			}

			if(!$use_next_page)
				self::$f->page_params[$id]=$result;
			else
			{
				if(!empty($result))
					self::$f->page_params[$id]=$result;
				else
				{
					$id--;
					while(empty($result)&&$id>0)
					{
						foreach($all_pages as $k=> $v)
						{
							if($v[10]=='<id>'.$id)
							{
								$result=$v;
								break;
							}
						} $id--;
					}
				}
			}
		}
		return $result;
	}

	public static function defineAdminLink($pinfo)
	{
		$admin_link='';
		if($pinfo[4]=='18')
		{
			$dir=(strpos($pinfo[1],'../')===false)?'':'../'.Formatter::GFS($pinfo[1],'../','/').'/';
			$admin_link=$dir.'ezgmail_'.str_replace('<id>','',$pinfo[10]).'.php?action=index';
		}
		elseif($pinfo[4]=='133')
		{
			$dir=(strpos($pinfo[1],'../')===false)?'':'../'.Formatter::GFS($pinfo[1],'../','/').'/';
			$admin_link=$dir.'newsletter_'.str_replace('<id>','',$pinfo[10]).'.php?action=subscribers';
		}
		elseif($pinfo[4]=='143'&&strpos($pinfo[1],'?flag=podcast')!==false)
		{
			$admin_link=$pinfo[1].'&action=index';
		}
		elseif($pinfo[4]=='20')
		{
			if(strpos($pinfo[1],'action=show')!==false)
				$admin_link=str_replace('action=show','action=doedit',$pinfo[1]);
			else
				$admin_link=$pinfo[1].'?'.'action=doedit';
		}
		elseif(in_array($pinfo[4],self::$f->sp_pages_ids))
			$admin_link=$pinfo[1].'?action=index';
		else
			$admin_link=$pinfo[1];

		return $admin_link;
	}

	public static function formatCaption($caption,$p=false)
	{
		$result='<span class="rvts8 a_editcaption">'.$caption.'</span>';
		if($p)
			$result='<p>'.$result.'</p>';
		return $result;
	}

	public static function formatNotice($notice,$p=false)
	{
		$result='<span class="rvts8 a_editnotice">'.$notice.'</span>';
		if($p)
			$result='<p>'.$result.'</p>';
		return $result;
	}

	public static function getMyprofileLabels($thispage_id,$root_path='../',$lang='')
	{
		$labels=array();
		if($thispage_id!=''&&$thispage_id>0)
		{
			$pageid_info=self::getPageParams($thispage_id,$root_path);
			if(empty($pageid_info))
			{
				for($i=1; $i<=7; $i++)
				{
					$pageid_info=self::getPageParams(($thispage_id-$i),$root_path);
					if(!empty($pageid_info))
						break;
				}
			}
			if($lang=='')
				$lang=(isset($pageid_info[22]))?$pageid_info[22]:'EN';
			$key=array_search($lang,self::$f->inter_languages_a);
			if($key!==false)
				$labels=self::$f->lang_reg[$key];
			if(empty($labels)&&$lang=='EN')
				$labels=self::$f->lang_reg['EN'];
		}
		else
			$labels=self::$f->lang_reg['0'];
		return $labels;
	}

}

class History extends FuncHolder
{
	public static function getPath($root)
	{
		return ($root?'':'../').'innovaeditor/assets/admin/history/';
	}

	public static function getFilePath($root,$page_id,$entry_id)
	{
		return self::getPath($root).$page_id.'_'.$entry_id;
	}

	public static function addFlat($root,$page_id,$entry_id,$user_id,$data)
	{
		$history_path=self::getPath($root);
		$history_filepath=self::getFilePath($root,$page_id,$entry_id);

		$go=true;
		if(!is_dir($history_path))
			if(!@mkdir($history_path,0700))
				$go=false;

		if($go)
		{
			$date=Date::buildMysqlTime();
			$file_contents='<entry date="'.$date.'" user="'.$user_id.'">'.$data.'</entry>'.F_LF;

			$fp=fopen($history_filepath,'a+');
			if($fp)
			{
				fwrite($fp,$file_contents);
				fclose($fp);
			}
		}
	}

}

class User extends FuncHolder
{

	private $id;
	private $uname;
	private $access;
	private $news;
	private $data;
	private $isAdmin;

	public function __construct()
	{
		parent::__construct();
		$this->id=null;
		$this->uname=null;
		$this->data=null;
		$this->access=null;
		$this->news=null;
		$this->isAdmin=null;
	}

	public static function isEZGAdminLogged() //true if admin logged
	{
		return Cookie::isAdmin()&&(!self::isLogged('HTTP_USER_AGENT')||Session::getVar('HTTP_USER_AGENT')==md5($_SERVER['HTTP_USER_AGENT']));
	}

	public function isEZGAdminNotLogged()
	{
		return (!Cookie::isAdmin()||self::isLogged('HTTP_USER_AGENT')&&($_SESSION['HTTP_USER_AGENT']!=md5($_SERVER['HTTP_USER_AGENT'])));
	}

//user logged cookies
	public function userCookie()
	{
		return self::isLogged(self::$f->user_cookieid);
	}

	public function getUserCookie()
	{
		return Session::getVarStr(self::$f->user_cookieid);
	}

	public static function setUserCookie($c)
	{
		Session::setVar(self::$f->user_cookieid,$c);
	}

	public static function isLogged($Var)
	{
		$sessVar=Session::getVar($Var);
		$issetVar=($sessVar!=''||$sessVar!=NULL);
		return $issetVar;
	}

	public static function formatUsers($users,$user_as_index=false,$userid_as_index=false)  //flat only, also used in data.php for import
	{
		$users_array=array();
		$i=1;

		while(strpos($users,'<user id="')!==false)
		{
			$i=Formatter::GFS($users,'<user id="','" ');
			$all='<user id="'.$i.'" '.Formatter::GFS($users,'<user id="'.$i.'" ','</user>');
			$basic=Formatter::GFS($all,'<user id="'.$i.'" ','>').' ';
			$details=Formatter::GFS($all,'<details ','></details>').' ';
			$access=Formatter::GFS($all,'<access_data>','</access_data>').' ';
			$news=Formatter::GFS($all,'<news_data>','</news_data>').' '; // event manager

			list($username,$password)=explode(' ',$basic);
			$details_arr=array();
			$details_arr['email']=Formatter::GFS($details,'email="','"');
			$details_arr['first_name']=Formatter::GFS($details,'name="','"');
			$details_arr['surname']=Formatter::GFS($details,'sirname="','"');
			$details_arr['creation_date']=Formatter::GFS($details,'date="','"');
			$details_arr['self_registered']=Formatter::GFS($details,'sr="','"'); //self-registration flag

			$status_flag=Formatter::GFS($details,'status="','"');
			$details_arr['status']=($status_flag!='')?$status_flag:'1'; //status flag

			$access_arr=array();
			$j=1;
			while(strpos($access,'<access id="'.$j.'" ')!==false)
			{
				$access_full=Formatter::GFSAbi($access,'<access id="'.$j.'" ','</access>');
				$page_access_arr=array();
				$m=1;
				while(strpos($access_full,'<p id="'.$m.'" ')!==false)
				{
					$page_access_str=Formatter::GFSAbi($access_full,'<p id="'.$m.'" ','>');
					$page_access_arr []=array('page'=>Formatter::GFS($page_access_str,'page="','"'),'type'=>Formatter::GFS($page_access_str,'type="','"'));
					$m++;
				}
				$access_str=Formatter::GFS($access_full,'<access id="'.$j.'" ','>');
				list($section,$type)=explode(' ',$access_str);
				$access_arr[]=array(substr($section,0,strpos($section,'='))=>Formatter::GFS($section,'="','"'),substr($type,0,strpos($type,'='))=>Formatter::GFS($type,'="','"'),'page_access'=>$page_access_arr);
				$j++;
			}
			$news_arr=array();
			$j=1; // event manager
			while(strpos($news,'<news id="'.$j.'" ')!==false)
			{
				$news_str=Formatter::GFS($news,'<news id="'.$j.'" ','>');
				list($page,$cat)=explode(' ',$news_str);
				$news_arr []=array(substr($page,0,strpos($page,'='))=>Formatter::GFS($page,'="','"'),substr($cat,0,strpos($cat,'='))=>Formatter::GFS($cat,'="','"'));
				$j++;
			}

			$user=Formatter::GFS($username,'="','"');
			if($user_as_index)
			{
				$users_array[$user]=array('id'=>$i,'uid'=>$i,'username'=>$user,'password'=>Formatter::GFS($password,'="','"'),'access'=>$access_arr,'news'=>$news_arr);
				foreach($details_arr as $k=> $v)
					$users_array[$user][$k]=$v;
			}
			elseif($userid_as_index)
			{
				$users_array[$i]=array('id'=>$i,'uid'=>$i,'username'=>$user,'password'=>Formatter::GFS($password,'="','"'),'access'=>$access_arr,'news'=>$news_arr);
				foreach($details_arr as $k=> $v)
					$users_array[$i][$k]=$v;
			}
			else
			{
				$usr=array('id'=>$i,'uid'=>$i,'username'=>$user,'password'=>Formatter::GFS($password,'="','"'),'access'=>$access_arr,'news'=>$news_arr);
				foreach($details_arr as $k=> $v)
					$usr[$k]=$v;
				$users_array[]=$usr;
			}

			$users=str_replace($all,'',$users);
		}
		return $users_array;
	}

	public static function getAllUsers($root_path,$user_as_index=false,$userid_as_index=false,$add_admin=false,$db=null)
	{
		$users_arr=array();

		$filename=(strpos($root_path,'centraladmin.ezg.php')!==false)?$root_path:$root_path.self::$f->ca_db_fname;
		$src=File::read($filename);
		$users=Formatter::GFS($src,'<users>','</users>');
		if($users!='')
			$users_arr=self::formatUsers($users,$user_as_index,$userid_as_index);

		if($add_admin)
			$users_arr[-1]=array('uid'=>'-1','username'=>self::$f->admin_nickname,'avatar'=>self::$f->admin_avatar);
		return $users_arr;
	}

	public static function getUser($username,$root_path,$by_email='',$by_id='')
	{
		$specific_user=false;

		if(self::isEZGAdminLogged())
		{
			$specific_user['uid']=-1;
			$specific_user['username']=self::$f->admin_nickname!=''?self::$f->admin_nickname:'admin';
			$specific_user['email']=self::$f->admin_email;
			$specific_user['avatar']=self::$f->admin_avatar;
			$specific_user['user_admin']=0;

			return $specific_user; //admin found, end of story
		}

		$users_arr=self::getAllUsers($root_path);
		if(!empty($users_arr))
		{
			if($by_email!='')
			{
				foreach($users_arr as $k=> $v)
				{
					if($v['email']==$by_email)
					{
						$specific_user=$v;
						break;
					}
				}
			}
			elseif($by_id!='')
			{
				foreach($users_arr as $k=> $v)
				{
					if($v['id']==$by_id)
					{
						$specific_user=$v;
						break;
					}
				}
			}
			else
			{
				foreach($users_arr as $k=> $v)
				{
					if($v['username']==$username)
					{
						$specific_user=$v;
						break;
					}
				}
			}
		}

		return $specific_user;
	}

	public function mGetLoggedAs() //gets logged user name (even if it's admin)
	{
		if($this->uname!==null) return $this->uname;
		$result='';
		if($this->userCookie())
			$result=$this->getUserCookie();
		elseif(Cookie::isAdmin())
			$result=self::$f->admin_nickname!=''?self::$f->admin_nickname:'admin';
		if($result!='') $this->uname=$result;
		return $result;
	}

	public function mGetLoggedUser($db,$admin_email=false,$full_data=false)
	{
		if($this->data!==null) return $this->data;
		if(self::$f->admin_email===false)
			$admin_email=self::$f->admin_email;
		$userData = false;
		if($this->userCookie())
		{
			$userData = $this->getUser($this->getUserCookie(),$db,'username',$full_data,$full_data);
		}
		elseif($this->isEZGAdminLogged())
		{
			$ua=array();
			$ua['uid']=-1;
			$ua['username']=self::$f->admin_nickname!=''?self::$f->admin_nickname:'admin';
			$ua['email']=$admin_email;
			$ua['avatar']=self::$f->admin_avatar;
			$ua['user_admin']=0;
			$userData = $ua;
		}
		if($userData) $this->data=$userData;
		return $userData;
	}

	public function mGetUserID($db)
	{
		if($this->id!==null) return $this->id;
		$result=0;
		if($this->userCookie())
		{
			$user_account=$this->getUser($this->getUserCookie(),$db,'username',false,false);
			$result=$user_account['uid'];
			$this->id = $result;
		}
		return $result;
	}

	public function getUserPG($page_id,$root_path)
	{
		$result=array();
		$all_users=$this->getAllUsers($root_path.self::$f->ca_db_fname);
		$page_info=CA::getPageParams($page_id,$root_path.self::$f->sitemap_fname);
		foreach($all_users as $user)
		{
			if(isset($user['access'][0])&&$user['access'][0]['section']=='ALL')
				$result[]=$user['username'];
			else
			{
				foreach($user['access'] as $v)
				{
					if($page_info[7]==$v['section']||!self::$f->use_prot_areas)
					{
						if($page_info[7]==$v['section']&&$v['type']!='2')
							$result[]=$user['username'];
						elseif(isset($v['page_access']))
						{
							foreach($v['page_access'] as $vv)
							{
								if($page_id==$vv['page']&&$vv['type']!='2')
								{
									$result[]=$user['username'];
									break;
								}
							}
						}
					}
				}
			}
		}
		$result=array_unique($result);
		return $result;
	}

	public function fetchUserID($username,$rel_path)
	{
		$user_data=$this->getUser($username,$rel_path);
		return (!empty($user_data)?$user_data['uid']:0);
	}

	public function fetchUserName($user_id,$rel_path)
	{
		$user_data=$this->getUser($user_id,$rel_path,'',$user_id);
		return (isset($user_data['display_name'])&&!empty($user_data['display_name'])?$user_data['display_name']:(isset($user_data['username'])?$user_data['username']:''));
	}

	public function hasWriteAccess($username,$page_info,$root_path='../')
	{
		$access=false;
		$page_id=str_replace('<id>','',$page_info[10]);
		$user_account=$this->getUser($username,$root_path.self::$f->ca_db_fname);

		if(!empty($user_account)&&($user_account['status']=='1'))
		{
			if(isset($user_account['access'][0])&&$user_account['access'][0]['section']!='ALL'&&$user_account['username']==$username)
			{
				foreach($user_account['access'] as $v)
				{
					if($page_info[7]==$v['section']||!self::$f->use_prot_areas)
					{
						if($page_info[7]==$v['section']&&$v['type']=='1')
						{
							$access=true;
						}
						elseif($v['type']=='2'&&isset($v['page_access']))
						{
							foreach($v['page_access'] as $val)
							{
								if($page_id==$val['page']&&($val['type']=='1'||$val['type']=='3'))
								{
									$access=true;
									break;
								}
								elseif($page_id==$val['page']&&$val['type']=='2')
								{
									break;
								}
							}
						}
						if($page_info[7]==$v['section'])
							break;
					}
				}
			}
			elseif($user_account['username']==$username)
			{
				if(isset($user_account['access'][0])&&$user_account['access'][0]['type']=='1')
					$access=true;
			}
		}
		return $access;
	}

	public function hasRegisterAccess($username,$page_info,$root_path='../')
	{
		$auth=false;
		$user_account=$this->getUser($username,$root_path);
		if(!empty($user_account)&&($user_account['status']=='1'))
		{
			if(isset($user_account['access'][0])&&$user_account['access'][0]['section']!='ALL'&&$user_account['username']==$username)
			{
				foreach($user_account['access'] as $v)
				{
					if($page_info[7]==$v['section']||!self::$f->use_prot_areas)
					{
						$auth=true;
						break;
					}
				}
			}
			elseif($user_account['username']==$username)
				$auth=true;
		}
		return $auth;
	}

	public function userEditOwn($db,$uid,$page_info)
	{
		$ua=array();
		$where='user_id= '.$uid;
		$users_access=$this->getUserAccess($db,$where);
		if(isset($users_access[$uid]))
			$ua['access']=$users_access[$uid];
		return $this->userEditOwnCheck($ua,$page_info);
	}

	public function userEditOwnCheck($user_account,$page_info)
	{
		$result=false;
		$page_id=str_replace('<id>','',$page_info[10]);

		if(!empty($user_account)&&isset($user_account['access'][0])&&$user_account['access'][0]['section']!='ALL')
		{
			foreach($user_account['access'] as $v)
			{
				if($page_info[7]==$v['section']||!self::$f->use_prot_areas)
				{
					if($v['type']=='2'&&isset($v['page_access']))
					{
						foreach($v['page_access'] as $val)
						{
							if($page_id==$val['page']&&$val['type']=='3')
							{
								$result=true;
								break;
							}
						}
					}
				}
			}
		}
		return $result;
	}

	public function getUserName($user_id,$rel_path='../')
	{
		if($user_id==-1)
			return self::$f->admin_nickname!=''?self::$f->admin_nickname:"admin";
		else
		{
			$user_data=$this->getUser($user_id,$rel_path,'',$user_id);
			return (!empty($user_data)?$user_data['username']:'');
		}
	}

	public function getUserID($username,$rel_path='../')
	{
		$user_data=$this->getUser($username,$rel_path);
		return (!empty($user_data)?$user_data['uid']:0);
	}

	public function mGetLoggedValues($rel_path,$db)
	{
		global $admin_email;
		$user_data=array();
		if(Cookie::isAdmin())
		{
			$user_data['name']=self::$f->admin_nickname;
			$user_data['email']=$admin_email;
			$user_data['avatar']=self::$f->admin_avatar;
		}
		elseif($this->userCookie())
		{
			$username=$this->getUserCookie();
			$user_data=$this->getUser($username,$db,'username',false,false);
			$user_data['name']=isset($user_data['first_name'])?$user_data['first_name']:$username;
			foreach($user_data as $k=> $v)
			{
				if(strpos(self::$f->hidden_uf,'|'.$k.'|')!==false)
					unset($user_data[$k]);
			}
		}
		if(isset($user_data['email'])&&strpos($user_data['email'],'<')!=false)
			$user_data['email']=Formatter::GFS($user_data['email'],'<','>');
		return $user_data;
	}

	public function getLoggedData($rel_path,&$name_v,&$email_v,&$surname_v)
	{
		global $admin_email;
		if(Cookie::isAdmin())
		{
			$name_v='admin';
			$email_v=$admin_email;
		}
		elseif($this->userCookie())
		{
			$name_v=$this->getUserCookie();
			$user_data=$this->getUser($name_v,$rel_path);
			$email_v=$user_data['email'];
			if(isset($user_data['first_name'])&&!empty($user_data['first_name']))
			{
				$name_v=Formatter::unEsc($user_data['first_name']);
				$surname_v=Formatter::unEsc($user_data['surname']);
			}
		}
		if(strpos($email_v,'<')!=false)
			$email_v=Formatter::GFS($email_v,'<','>');
	}

	public function replaceUserFields($page,$db)
	{

		$user=$this->getUserCookie();
		if($user!='')
		{
			$user_data=$this->getUser($user,$db,'username',false,false);
			foreach($user_data as $k=> $v)
			{
				if(strpos(self::$f->hidden_uf,'|'.$k.'|')==false)
				{
					$rep_array=array();
					$rep_array[]=$k;
					if($k=='surname')
						$rep_array[]='last_name';
					elseif($k=='address')
						$rep_array[]='address1';
					foreach($rep_array as $vv)
						$page=str_replace('name="'.$vv.'"','name="'.$vv.'" value="'.$v.'"',$page);
				}
			}
		}
		return $page;
	}

}

//init $f
$f=new FuncConfig();
$f->counter_images=array('15|20','15|18','15|19','9|13','15|13','12|14','6|7','11|11','15|20','14|18');
$f->page_params=array();
$f->avatar_size='32';
$f->admin_email='';
$f->ca_settings=array();
$f->ca_fullscreen=true;
$f->url_fopen=ini_get('allow_url_fopen')!='off';
$f->hidden_uf='|password|creation_date|status|confirmed|self_registered|self_registered_id|details|';
$f->browsers=array('Unknown','IE','Opera','Firefox','Search Bot','AOL','Safari','Konqueror','IE 5','IE 6','IE 7','Opera 7','Opera 8','Firefox 1','Firefox 2','Netscape 6','Netscape 7','Firefox 3','Chrome','IE 8','IE 9','Firefox 4','Firefox 5','Firefox 6','Firefox 7','Firefox 8','Firefox 9','Firefox 10','Firefox 11','Firefox 12','IE 10','Mercury');
$f->navtop='<div class="a_n a_navtop"><!--pre-nav--><div class="a_navt">';
$f->navlist='<div class="a_n a_listing"><div class="a_navt">';
$f->navend='</div><!--post-nav--></div>';
$f->ca_rel_path=(isset($rel_path)&&$rel_path==''?'':'../');
$f->proj_id='437396863756366';
$f->admin_cookieid='SID_ADMIN'.$f->proj_id;
$f->user_cookieid='cur_user'.$f->proj_id;
$f->site_url=''; //don't rely on this, it's user-defined
$f->proj_pre='';
$f->counter_on=false;
$f->db=null;
$f->db_createcharset='';
$f->db_namescharset='';
$f->db_folder='ezg_data/';
$f->ca_db_fname=$f->db_folder.'centraladmin.ezg.php';
$f->ca_settings_fname=$f->ca_rel_path.$f->db_folder.'centraladmin_conf.ezg.php';
$f->sitemap_fname='sitemap.php';
$f->template_source='documents/template_source.html';
$f->max_chars=25000;
$f->cap_id='CAPTCHA_CODE';
$f->home_page='index.html';
$f->intro_page='';
$f->mysql_host='';
$f->mysql_dbname='';
$f->mysql_username='';
$f->mysql_password='';
$f->mysql_setcharset=false;
$f->use_mysql=false;
$f->mail_type="mail";
$f->SMTP_HOST='%SMTP_HOST%';
$f->SMTP_PORT='%SMTP_PORT%';
$f->SMTP_HELLO='%SMTP_HELLO%';
$f->SMTP_AUTH=('%SMTP_AUTH%'=='TRUE');
$f->SMTP_AUTH_USR='%SMTP_AUTH_USR%';
$f->SMTP_SECURE='%SMTP_SECURE%';
$f->site_charsets_a=array("UTF-8");
$f->site_languages_a=array("ENGLISH");
$f->inter_languages_a=array("EN");
$f->time_format_a=array("24");
$f->date_format_a=array("dd.mm.yy");
$f->ca_nav_labels=array('home'=>'+','first'=>' &lt;&lt; ','prev'=>' &lt; ','next'=>' &gt; ','last'=>' &gt;&gt; ');
$f->uni=('TRUE'=='TRUE');
$f->use_mb=($f->uni&&function_exists('mb_strtolower'));
$f->SMTP_AUTH_PWD='%SMTP_AUTH_PWD%';
$f->return_path='';
$f->use_hostname=false;
$f->sendmail_from='';
if(isset($_SERVER['SERVER_SOFTWARE']))
	$f->use_linefeed=(strpos($_SERVER['SERVER_SOFTWARE'],'Microsoft')!==false)||(strpos($_SERVER['SERVER_SOFTWARE'],'Win')!==false);
else
	$f->use_linefeed=false;
$f->lf=($f->use_linefeed?"\r\n":"\n");
define('F_LF',$f->lf);
$f->xhtml_on=false;
$f->html='HTML5';
$f->ct=($f->xhtml_on?" />":">");
define('F_BR','<br>');
$f->js_st=($f->xhtml_on?"/* <![CDATA[ */":"<!--");
$f->js_end=($f->xhtml_on?"/* ]]> */":"//-->");
$f->php_timezone='';
$f->def_tz_set=false;
if($f->php_timezone!=''&&function_exists('date_default_timezone_set'))
{
	date_default_timezone_set($f->php_timezone);
	$f->def_tz_set=true;
}
if($f->php_timezone==''&&function_exists('date_default_timezone_get'))
{
	$f->php_timezone=date_default_timezone_get();
}
$f->tzone_offset=-10000;
$f->names_lang_sets=array('BG'=>'Bulgarian','CS'=>'Czech','DA'=>'Danish','NL'=>'Dutch','EN'=>'English','ET'=>'Estonian','FI'=>'Finnish','FR'=>'French','DE'=>'German','EL'=>'Greek','HE'=>'Hebrew','HU'=>'Hungarian','IS'=>'Icelandic','IT'=>'Italian','NO'=>'Norwegian','PL'=>'Polish','PT'=>'Portuguese','RU'=>'Russian','SK'=>'Slovak','SL'=>'Slovenian','ES'=>'Spanish','SV'=>'Swedish','ZH'=>'Chinese','UK'=>'Ukrainian','BP'=>'Brazilian'); //'CA'=>'Catalan','RO'=>'Romanian',
$f->charset_lang_map=array('BG'=>'Windows-1251','CS'=>'Windows-1250','DA'=>'iso-8859-1','NL'=>'iso-8859-1','EN'=>'iso-8859-1','ET'=>'Windows-1257','FI'=>'iso-8859-1','FR'=>'iso-8859-1','DE'=>'iso-8859-1','EL'=>'Windows-1253','HE'=>'Windows-1255','HU'=>'Windows-1250','IS'=>'iso-8859-1','IT'=>'iso-8859-1','NO'=>'iso-8859-1','PL'=>'Windows-1250','PT'=>'iso-8859-1','RU'=>'Windows-1251','SK'=>'Windows-1250','SL'=>'windows-1250','ES'=>'iso-8859-1','SV'=>'iso-8859-1');
$f->innova_lang_list=array('english'=>'en-US','danish'=>'da-DK','german'=>'de-DE','spanish'=>'es-ES','finnish'=>'fi-FI','french'=>'fr-FR','norwegian'=>'nn-NO','italian'=>'it-IT','swedish'=>'sv-SE','dutch'=>'nl-NL');
$f->innova_asset_def_size='600';
$f->day_names=array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
$f->month_names=array("January","February","March","April","May","June","July","August","September","October","November","December");
$f->max_rec_on_admin=20;
$f->use_captcha=true;
$f->captcha_size='small';  //captcha size is actually captcha type in ezg since v 4.0.0.402
$f->bg_tag='background: #ffffff;';
$f->atbg_class='t1';
$f->atbgr_class='t3';
$f->atbgc_class='t2';
$f->ftm_title='<span class="rvts8 a_editcaption">%s</span>'.F_BR;
$f->fmt_star='<em style="color:red;">*</em>';
$f->fmt_hidden='<input type="hidden" name="%s" value="%s">';
$f->fmt_input_p='<input class="input1" type="text" name="%s" value="%s" style="width:500px" maxlength="255">'.F_BR; //used in add post
$f->fmt_input_com='<input class="comments_input input1" type="text" name="%s" value="%s" id="comments_%s" maxlength="50" style="width: 98%%;" >'; // used in add comment
$f->fmt_caption='<span class="a_tabletitle">%s</span>';
$f->search_templates_a=array("0");
$f->ca_profile_templates_a=array("0");
$f->error_iframe=false; //the iframe html string or FALSE if not used.
$f->tiny=false;
$f->editor="LIVE";
$f->ext_styles=array("normal","sub","L","XL","XXL","XXXL","43746,564961956");
//captcha size is actually captcha type in ezg
$f->reCaptcha=$f->captcha_size=='recaptcha';
$f->slidingCaptcha=$f->captcha_size=='sliding captcha';

$f->captchajs='var captcha=$(document).ready(function(){loadCaptcha("%PATH%");});';
$f->frmvalidation='$(document).ready(function(){
    $("form[id^=%ID%]").each(function(){
        var frl=$(this),frl_id=$(this).attr("id");
        if(frl!=null){
            frl.append(\'<input type="hidden" id="cc" name="cc" value="1"/>\'); 
            frl.submit(function(event){ 
                event.preventDefault(); 
                $(".frmhint").empty(); 
                $("#"+frl_id+" .input1").removeClass("inputerror"); 
                $.post( frl.attr("action"), frl.serialize(), function(re){ 
                    if(re.charAt(0)=="1"){ 
                        msg=re.substring(1).split("|"); 
                        if(msg[1]) 
                           alert(msg[1]);
                        cc=$("#"+frl_id+" #cc"); 
                        cc.val("0"); 
                        frl.unbind("submit"); 
                        frl.submit(); 
                        cc.val("1"); 
                    }
                    else if(re.charAt(0)=="0") { 
                        errors=re.substring(1).split("|"); 
                        for(i=0;i<errors.length;i=i+2) { 
                            $("#"+frl_id+"_"+errors[i]).append(errors[i+1]); 
                            $("#"+frl_id+" input[name="+errors[i]+"]").addClass("inputerror"); 
                        } 
                        if(typeof reloadReCaptcha=="function") reloadReCaptcha(); 
                    } 
                }); 
            }); 
        } 
    }); 
});';
$f->frmvalidation2='<script type="text/javascript">$(document).ready(function(){frl=$("#%ID%");if(frl!=null){frl.append(\'<input type="hidden" id="cc" name="cc" value="1"/>\');frl.submit(function(event){event.preventDefault();$(".frmhint").empty();$("#%ID% input").removeClass("inputerror");$("#div_%ID%").addClass("ajl").css("opacity","0.2").delay(500);$.post(frl.attr("action"),frl.serialize(),function(re){$("#div_%ID%").removeClass("ajl").css("opacity","1");if(re.charAt(0)=="1"){cc=$(".%ID% #cc");cc.val("0");frl.unbind("submit");frl.submit();cc.val("1");}else if(re.charAt(0)=="0") {errors=re.substring(1).split("|");for(i=0;i<errors.length;i=i+2) {$("#%ID%_"+errors[i]).append("<br />"+errors[i+1]);$("#%ID% input[name="+errors[i]+"]").addClass("inputerror");} if(typeof reloadReCaptcha == "function") reloadReCaptcha(); }else $("#%ID%").html(re);});})}});$(document).ready(function(){$(".passreginput").pmeter();})</script>';
$f->loginvalidation='<script type="text/javascript">$(document).ready(function(){frl=$("#%ID%");if(frl!=null){frl.append(\'<input type="hidden" id="cc" name="cc" value="1"/>\');frl.submit(function(event){event.preventDefault();$(".frmhint").empty();$("#%ID% input").removeClass("inputerror");$.post(frl.attr("action"),frl.serialize(),function(re){if(re.charAt(0)=="1"){cc=$(".%ID% #cc");cc.val("0");frl.unbind("submit");frl.submit();cc.val("1");}else if(re.charAt(0)=="0") {errors=re.substring(1).split("|");for(i=0;i<errors.length;i=i+2) {$("#%ID%_"+errors[i]).append("<br />"+errors[i+1]);$("#%ID% input[name="+errors[i]+"]").addClass("inputerror");} if(typeof reloadReCaptcha == "function") reloadReCaptcha();  }});})}});</script>';
$f->md_dialog='var activeModalWin; var isAssetOpened; function mDialogShow(url,width,height){var left=screen.availWidth/2-width/2;var top=screen.availHeight/2 - height/2;activeModalWin= window.open(url, "", "width="+width+"px,height="+height+",left="+left+",top="+top);window.onfocus=function(){if(activeModalWin.closed==false){activeModalWin.focus();};};}'
	."function openAsset(id){cmdAManager=\"".($f->tiny?'mDialogShow':'modalDialogShow')."('%sinnovaeditor/assetmanager/assetmanager.php?lang=%s&root=%s&id=\'+id,755,500)\";eval(cmdAManager); isAssetOpened= true;}"
	."function setAssetValue(val,id){document.getElementById(id).value=val;ima=document.getElementById('ima_'+id); if(ima!=null){ima.src=val;ima.style.display='block';}}";

$f->editor_js=<<<MSG
<script type="text/javascript" src="%RELPATH%innovaeditor/scripts/language/%XLANGUAGE%/editor_lang.js"></script>
<script type="text/javascript" src='%RELPATH%innovaeditor/scripts/innovaeditor.js'></script>
MSG;
$f->editor_html=<<<MSG
<script type="text/javascript">
var oEdit1=new InnovaEditor("oEdit1");oEdit1.width="100%";oEdit1.height="350px";%RTL%
var dummy;
oEdit1.arrCustomButtons=[["Snippets","modalDialog('%RELPATH%innovaeditor/bootstrap/snippets.htm',900,658,'Insert Snippets');", "Snippets", "btnContentBlock.gif"]];
oEdit1.groups = [
    ["group1","",["FontName","FontSize","Superscript","ForeColor","BackColor","FontDialog","BRK","Bold","Italic", "Underline", "Strikethrough", "CompleteTextDialog", "Styles", "RemoveFormat"]],
    ["group2","",["JustifyLeft", "JustifyCenter", "JustifyRight", "Paragraph", "BRK", "Bullets", "Numbering", "Indent", "Outdent"]],
    ["group3","",["TableDialog", "Emoticons", "FlashDialog","CharsDialog", "BRK", "LinkDialog", "ImageDialog", "YoutubeDialog","Line"]],
    ["group4","",["SearchDialog", "SourceDialog","Paste","BRK","Undo","Redo"]]];
oEdit1.arrStyle=[["BODY",false,"","font: 15px Gulim;color:#000000;%BACKGROUND%"],["a",false,"","font: 15px Gulim;color:#400040;margin:0px;"],["p",false,"","text-indent:0px;padding:0px;margin:0px;"],["h1",false,"","font: bold 28px Gulim;color:#000000;margin:0px;"],["h2",false,"","font: bold 24px Gulim;color:#000000;margin:0px;"],["h3",false,"","font: bold 20px Gulim;color:#000000;margin:0px;"],["h4",false,"","font: bold 15px Gulim;color:#000000;margin:0px;"],["h5",false,"","font: bold 12px Gulim;color:#000000;margin:0px;"],["h6",false,"","font: 12px Gulim;color:#000000;margin:0px;"],["h6",false,"","font: 12px Gulim;color:#000000;margin:0px;"]];
oEdit1.flickrUser="ezgenerator";
oEdit1.css=["%RELPATH%innovaeditor/styles/default.css"];
if(typeof oEditFonts!=="undefined") for(var i=0;i<oEditFonts.length;i++) oEdit1.css.push("http://fonts.googleapis.com/css?family="+oEditFonts[i]);
oEdit1.fileBrowser="../../assetmanager/assetmanager.php?lang=%XLANGUAGE%&root=%RELPATH%";
oEdit1.customColors=["#ff4500","#ffa500","#808000","#4682b4","#1e90ff","#9400d3","#ff1493","#a9a9a9"];
oEdit1.mode="HTMLBody";oEdit1.REPLACE("htmlarea");
</script>
MSG;

$f->gfonts=array('Abel','Abril Fatface','Aclonica','Actor','Aldrich','Alike','Alice','Allan','Allerta','Allerta Stencil','Amaranth','Andika','Anonymous Pro','Antic','Anton','Architects Daughter','Arimo','Artifika',
	'Arvo','Asset','Astloch','Aubrey','Bangers','Bentham','Bevan','Bigshot One','Black Ops One','Bowlby One','Bowlby One SC','Brawler','Cabin','Cabin Sketch','Calligraffitti','Candal',
	'Cantarell','Cardo','Carme','Carter One','Changa One','Cedarville Cursive','Cherry Cream Soda','Chewy','Coda','Comfortaa','Coming Soon','Copse','Corben','Cousine','Coustard',
	'Covered By Your Grace','Crafty Girls','Crimson Text','Crushed','Cuprum','Damion','Days One','Delius','Delius Swash Caps','Delius Unicase','Didact Gothic','Dorsa','Droid Sans',
	'Droid Sans Mono','Droid Serif','EB Garamond','Expletus Sans','Fanwood Text','Federo','Fontdiner Swanky','Forum','Francois One','Goblin One','Gentium Basic','Gentium Book Basic',
	'Geo','Geostar','Geostar Fill','Give You Glory','Gloria Hallelujah','Goudy Bookletter 1911','Gravitas One','Gruppo','Hammersmith One','Holtwood One SC','Homemade Apple',
	'IM Fell DW Pica','IM Fell DW Pica SC','IM Fell Double Pica','IM Fell Double Pica SC','IM Fell English','IM Fell English SC','IM Fell French Canon',
	'IM Fell French Canon SC','IM Fell Great Primer','IM Fell Great Primer SC','Inconsolata','Indie Flower','Istok Web','Irish Grover','Josefin Sans','Josefin Slab','Judson',
	'Just Another Hand','Just Me Again Down Here','Kameron','Kelly Slab','Kenia','Kranky','Kreon','Kristi','La Belle Aurore','Lato','League Script','Leckerli One','Lekton','Limelight',
	'Lobster','Lobster Two','Lora','Loved by the King','Love Ya Like A Sister','Luckiest Guy','Maiden Orange','Mako','Marvel','Maven Pro','Meddon','MedievalSharp','Megrim',
	'Merriweather','Metrophobic','Michroma','Miltonian','Miltonian Tattoo','Modern Antiqua','Molengo','Monofett','Monoton','Montez','Mountains of Christmas','Muli','Neucha','Neuton',
	'News Cycle','Nixie One','Nobile','Nothing You Could Do','Nova Cut','Nova Flat','Nova Mono','Nova Oval','Nova Round','Nova Script','Nova Slim','Numans','Nunito',
	'OFL Sorts Mill Goudy TT','Old Standard TT','Open Sans','Open Sans Condensed','Orbitron','Oswald','Ovo','Pacifico','Passero One','Paytone One','Patrick Hand','Permanent Marker',
	'Philosopher','Play','Playfair Display','Podkova','Pompiere','Prociono','PT Sans','PT Sans Caption','PT Sans Narrow','PT Serif','Puritan','Quattrocento','Quattrocento Sans',
	'Questrial','Radley','Raleway','Rationale','Redressed','Reenie Beanie','Rochester','Rock Salt','Rokkitt','Rosario','Schoolbell','Shadows Into Light','Shanti','Short Stack','Sigmar One',
	'Six Caps','Slackey','Smokum','Smythe','Sniglet','Snippet','Special Elite','Stardos Stencil','Sunshiney','Syncopate','Tangerine','Tenor Sans','Terminal Dosis Light','Tienne','Tinos',
	'Tulpen One','Ubuntu','Ultra','UnifrakturCook','UnifrakturMaguntia','Unkempt','Unna','Varela','Varela Round','Vibur','Vidaloka','Volkhov','Vollkorn','Voltaire','VT323','Waiting for the Sunrise',
	'Wallpoet','Walter Turncoat','Wire One','Yanone Kaffeesatz','Yellowtail','Yeseva One','Zeyada');

$f->buttonhtml='<a class="e_button" href="">%BUTTON%</a>';
$f->ttype=2;
$f->smenu='<li><a class="smenu" href="%MenuItemUrl%">%MenuItemText%</a></li>';
$f->ssmenu='<li><a class="ss2menu" href="%MenuItemUrl%">%MenuItemText%</a></li>';
$f->http_prefix=(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']=='on')?'https://':'http://';
$f->httpRedirect=false;
$f->db_charset='utf8'; // used for CA,counter,search
$f->os=array('Unknown','Win95','Win98','WinNT','W2000','WinXP','W2003','Vista','Linux','Mac','Windows','Win 7','iOS','Search Bot','android','Win 8','BlackBerry');
$f->admin_nickname='admin';
$f->admin_nickname=($f->admin_nickname=='')?'admin':$f->admin_nickname;
$f->admin_avatar='';
$f->lang_reg=array("0"=>array("welcome"=>"welcome [%%username%%]","profile"=>"profile","administration panel"=>"administration","protected area"=>"Protected area login","login form msg"=>"Please Login!","login"=>"log in","forgot password"=>"Forgot your password?","not a member"=>"Not a member yet?","register"=>"Register","welcome guest"=>"Welcome Guest","use correct username"=>"Please, use correct username and password to log in. You have %%attempt%% more attempts before account is blocked.","logout"=>"log out","username"=>"username","username exists"=>"such username already exists","unexisting"=>"This Username can't be found in the database","can contain only"=>"username can contain only A-Z, a-z, - _ @ . and 0-9","username equal password"=>"username can not be equal to password","name"=>"first name","surname"=>"last name","email"=>"email","email not found"=>"This Email address can't be found in the database","no email for user"=>"Email address is not defined for this Username. Please, contact the administrator.","password"=>"password","repeat password"=>"repeat password","password and repeated password"=>"password and repeated password don't match","change password"=>"change password","old password"=>"old password","new password"=>"new password","forgotten password"=>"forgotten password","forgot password message"=>"Enter Username OR Email address, and email with instructions for resetting password will be sent to you.","check email for new password"=>"Check your email to find the new password.","check email for instructions"=>"Check your email to find instructions for resetting password.","your password should be"=>"your password should be at least five symbols","registration"=>"Registration","registration was successful"=>"Your registration was successful. To complete it, check your email and follow the instructions.","registration was completed"=>"Your registration was successfully completed. ","you have to fill"=>"You have to fill either Email address or Username","required fields"=>"required fields","code"=>"verification code","I agree with terms"=>"I agree with the %%Terms of Use%%","you must agree with terms"=>"In order to proceed, you must agree with the Terms of Use","want to receive notification"=>"I want to receive notification for","site map"=>"sitemap","page name"=>"page name","admin link"=>"admin link","edit"=>"edit","save"=>"save","submit_btn"=>"submit","submit_register"=>"Register","submit_password"=>"Send","changes saved"=>"changes saved","close"=>"Close","my orders"=>"my orders","wrong_ext"=>"Only jpg/gif/png images are allowed!","short pwd"=>"Too short","weak"=>"Weak","average"=>"Average","good"=>"Good","strong"=>"Strong","forbidden"=>"Forbidden","email in use"=>"email in use","redirect in"=>"You will be redirected in %%time%% seconds","blocked_err_msg"=>"This account is blocked. Contact administrator!","temp_blocked_err_msg"=>"This account is temporarily blocked. Try again later.","unconfirmed_msg"=>"This account is not confirmed yet!","incorrect username/password"=>"incorrect username/password","require_approval"=>"self-registered users require activation from administrator","registration failed"=>"registration failed","incorrect credentials"=>"Please, use correct username and password to log in.","account_expired_msg"=>"your account expired!","remember me"=>"Remember me","fb login"=>"FB Login"));
$f->lang_f=array("0"=>array("Email not valid"=>"E-mail address is not valid. Please change it and try again...","Emails do not match"=>"Email confirmation does not match your Email","Required Field"=>"Required Field","Checkbox unchecked"=>"Field must be checked","Captcha Message"=>"Verification code does not match","validation failed"=>"Please correct the errors on this form.","post waiting approval"=>"Your message was posted, but waiting for approval. Once approved, it will appear on page.","login on comments"=>"Please Login to post comments!","dear"=>"Dear","email in use"=>"email in use","submit_btn"=>"submit","loading"=>"Loading...","total votes"=>"Total Votes","votes"=>"votes","ranking"=>"ranking","ranking mandatory"=>"Ranking is mandatory!"));
$f->innova_limited=false;
$f->tooltips_js=<<<MSG
\$(document).ready(function(){\$("a.hhint,td.hhint,label.hhint").cluetip({className:"hhint",width:200,arrows:true});});
MSG;
$f->use_prot_areas=false;
$f->sp_pages_ids=array('20','133','136','137','138','143','144','181','190','147');
$f->max_ranking=5;
$f->direct_ranking=false;
$f->ranking_script='$(document).ready(function(){$(".ranking").ranking({numbers:true});});';

$f->subminiforms=array('frm_2'=>'29');
$f->subminiforms_news=array();

$f->mobile_detected=false;
$f->ranking_average=true;  //when disabled, ranking is total (and not average)

$f->login_cb_str='vid';

$f->checked_users=array(); //holding already checked users to prevent multi-queries on user check

$f->comments_allowed_tags=array(
	'html'=>array('<p>','<u>','<i>','<b>','<strong>','<del>','<code>','<hr>'),
	'html_admin'=>array('<a>','<img>','<span>','<div>'),
	'extra'=>array('<span>','<div>')
);
// <editor-fold defaultstate="collapsed" desc="countries list">
$f->countries_list=array('AF'=>'Afghanistan'
	,'AL'=>'Albania'
	,'DZ'=>'Algeria'
	,'AS'=>'America Samoa'
	,'AD'=>'Andorra'
	,'AO'=>'Angola'
	,'AI'=>'Anguila'
	,'AQ'=>'Antartica'
	,'AG'=>'Antigua And Barbuda'
	,'AR'=>'Argentina'
	,'AM'=>'Armenia'
	,'AW'=>'Aruba'
	,'AU'=>'Australia'
	,'AT'=>'Austria'
	,'AZ'=>'Azerbaijan'
	,'BS'=>'Bahamas, The'
	,'BH'=>'Bahrain'
	,'BD'=>'Bangladesh'
	,'BB'=>'Barbados'
	,'BY'=>'Belarus'
	,'BE'=>'Belgium'
	,'BZ'=>'Belize'
	,'BJ'=>'Benin'
	,'BM'=>'Bermuda'
	,'BT'=>'Bhutan'
	,'BO'=>'Bolivia'
	,'BA'=>'Bosnia and Herzegovina'
	,'BW'=>'Botswana'
	,'BV'=>'Bouvet Island'
	,'BR'=>'Brazil'
	,'IO'=>'British Indian Ocean Territory'
	,'BN'=>'Brunei'
	,'BG'=>'Bulgaria'
	,'BF'=>'Burkina Faso'
	,'BI'=>'Burundi'
	,'KH'=>'Cambodia'
	,'CM'=>'Cameroon'
	,'CA'=>'Canada'
	,'CV'=>'Cape Verde'
	,'KY'=>'Cayman Islands'
	,'CF'=>'Central African Republic'
	,'TD'=>'Chad'
	,'CL'=>'Chile'
	,'CN'=>'China'
	,'CX'=>'Christmas Island'
	,'CC'=>'Cocos (Keeling) Islands'
	,'CO'=>'Colombia'
	,'KM'=>'Comoros'
	,'CG'=>'Congo'
	,'CD'=>'Congo, Democractic Republic of the '
	,'CK'=>'Cook Islands'
	,'CR'=>'Costa Rica'
	,'CI'=>'Cote DIvoire (Ivory Coast)'
	,'HR'=>'Croatia (Hrvatska)'
	,'CU'=>'Cuba'
	,'CY'=>'Cyprus'
	,'CZ'=>'Czech Republic'
	,'DK'=>'Denmark'
	,'DJ'=>'Djibouti'
	,'DM'=>'Dominica'
	,'DO'=>'Dominican Republic'
	,'EC'=>'Ecuador'
	,'EG'=>'Egypt'
	,'SV'=>'El Salvador'
	,'GQ'=>'Equatorial Guinea'
	,'ER'=>'Eritrea'
	,'EE'=>'Estonia'
	,'ET'=>'Ethiopia'
	,'FK'=>'Falkland Islands (Islas Malvinas)'
	,'FO'=>'Faroe Islands'
	,'FJ'=>'Fiji Islands'
	,'FI'=>'Finland'
	,'FR'=>'France'
	,'GF'=>'French Guiana'
	,'PF'=>'French Polynesia'
	,'TF'=>'French Southern Territories'
	,'GA'=>'Gabon'
	,'GM'=>'Gambia, The'
	,'GE'=>'Georgia'
	,'DE'=>'Germany'
	,'GH'=>'Ghana'
	,'GI'=>'Gibraltar'
	,'GR'=>'Greece'
	,'GL'=>'Greenland'
	,'GD'=>'Grenada'
	,'GP'=>'Guadeloupe'
	,'GU'=>'Guam'
	,'GT'=>'Guatemala'
	,'GN'=>'Guinea'
	,'GW'=>'Guinea-Bissau'
	,'GY'=>'Guyana'
	,'HT'=>'Haiti'
	,'HM'=>'Heard and McDonald Islands'
	,'HN'=>'Honduras'
	,'HK'=>'Hong Kong S.A.R.'
	,'HU'=>'Hungary'
	,'IS'=>'Iceland'
	,'IN'=>'India'
	,'ID'=>'Indonesia'
	,'IR'=>'Iran'
	,'IQ'=>'Iraq'
	,'IE'=>'Ireland'
	,'IL'=>'Israel'
	,'IT'=>'Italy'
	,'JM'=>'Jamaica'
	,'JP'=>'Japan'
	,'JO'=>'Jordan'
	,'KZ'=>'Kazakhstan'
	,'KE'=>'Kenya'
	,'KI'=>'Kiribati'
	,'KR'=>'Korea'
	,'KP'=>'Korea, North'
	,'KW'=>'Kuwait'
	,'KG'=>'Kyrgyzstan'
	,'LA'=>'Laos'
	,'LV'=>'Latvia'
	,'LB'=>'Lebanon'
	,'LS'=>'Lesotho'
	,'LR'=>'Liberia'
	,'LY'=>'Libya'
	,'LI'=>'Liechtenstein'
	,'LU'=>'Luxembourg'
	,'MO'=>'Macau S.A.R.'
	,'MK'=>'Macedonia'
	,'MG'=>'Madagascar'
	,'MW'=>'Malawi'
	,'MY'=>'Malaysia'
	,'MV'=>'Maldives'
	,'ML'=>'Mali'
	,'MT'=>'Malta'
	,'MH'=>'Marshall Islands'
	,'MR'=>'Mauritania'
	,'MU'=>'Mauritius'
	,'YT'=>'Mayotte'
	,'MX'=>'Mexico'
	,'FM'=>'Micronesia'
	,'MD'=>'Moldova'
	,'MC'=>'Monaco'
	,'MN'=>'Mongolia'
	,'MS'=>'Montserrat'
	,'MA'=>'Morocco'
	,'MZ'=>'Mozambique'
	,'MM'=>'Myanmar'
	,'NA'=>'Namibia'
	,'NR'=>'Nauru'
	,'NP'=>'Nepal'
	,'AN'=>'Netherlands Antilles'
	,'NL'=>'Netherlands, The'
	,'NC'=>'New Caledonia'
	,'NZ'=>'New Zealand'
	,'NI'=>'Nicaragua'
	,'NE'=>'Niger'
	,'NG'=>'Nigeria'
	,'NU'=>'Niue'
	,'NF'=>'Norfolk Island'
	,'MP'=>'Northern Mariana Islands'
	,'NO'=>'Norway'
	,'OM'=>'Oman'
	,'PK'=>'Pakistan'
	,'PW'=>'Palau'
	,'PA'=>'Panama'
	,'PG'=>'Papua new Guinea'
	,'PE'=>'Peru'
	,'PH'=>'Philippines'
	,'PN'=>'Pitcairn Island'
	,'PL'=>'Poland'
	,'PT'=>'Portugal'
	,'PR'=>'Puerto Rico'
	,'QA'=>'Qatar'
	,'RE'=>'Reunion'
	,'RO'=>'Romania'
	,'RU'=>'Russia'
	,'RW'=>'Rwanda'
	,'SH'=>'Saint Helena'
	,'KN'=>'Saint Kitts And Nevis'
	,'LC'=>'Saint Lucia'
	,'PM'=>'Saint Pierre and Miquelon'
	,'VC'=>'Saint Vincent And The Grenadines'
	,'WS'=>'Samoa'
	,'SM'=>'San Marino'
	,'ST'=>'Sao Tome and Principe'
	,'SA'=>'Saudi Arabia'
	,'RS'=>'Serbia'
	,'SN'=>'Senegal'
	,'SC'=>'Seychelles'
	,'SL'=>'Sierra Leone'
	,'SG'=>'Singapore'
	,'SK'=>'Slovakia'
	,'SI'=>'Slovenia'
	,'SB'=>'Solomon Islands'
	,'SO'=>'Somalia'
	,'ZA'=>'South Africa'
	,'GS'=>'South Georgia'
	,'ES'=>'Spain'
	,'LK'=>'Sri Lanka'
	,'SD'=>'Sudan'
	,'SR'=>'Suriname'
	,'SJ'=>'Svalbard And Jan Mayen Islands'
	,'SZ'=>'Swaziland'
	,'SE'=>'Sweden'
	,'CH'=>'Switzerland'
	,'SY'=>'Syria'
	,'TW'=>'Taiwan'
	,'TJ'=>'Tajikistan'
	,'TZ'=>'Tanzania'
	,'TH'=>'Thailand'
	,'TG'=>'Togo'
	,'TK'=>'Tokelau'
	,'TO'=>'Tonga'
	,'TT'=>'Trinidad And Tobago'
	,'TN'=>'Tunisia'
	,'TR'=>'Turkey'
	,'TM'=>'Turkmenistan'
	,'TC'=>'Turks And Caicos Islands'
	,'TV'=>'Tuvalu'
	,'UG'=>'Uganda'
	,'UA'=>'Ukraine'
	,'AE'=>'United Arab Emirates'
	,'UK'=>'United Kingdom'
	,'US'=>'United States'
	,'UM'=>'United States Minor Outlying Islands'
	,'UY'=>'Uruguay'
	,'UZ'=>'Uzbekistan'
	,'VU'=>'Vanuatu'
	,'VA'=>'Vatican City State (Holy See)'
	,'VE'=>'Venezuela'
	,'VN'=>'Vietnam'
	,'VG'=>'Virgin Islands (British)'
	,'VI'=>'Virgin Islands (US)'
	,'WF'=>'Wallis And Futuna Islands'
	,'YE'=>'Yemen'
	,'ZM'=>'Zambia'
	,'ZW'=>'Zimbabwe');
// </editor-fold>

$user=new User();
?>
