<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); ?>
<?php function tpl_hide_credits_hidden($creditsrequire) {
global $_G;?><?php
$return = <<<EOF
<div class="locked">
EOF;
 if($_G['uid']) { 
$return .= <<<EOF
{$_G['username']}
EOF;
 } else { 
$return .= <<<EOF
游客
EOF;
 } 
$return .= <<<EOF
，本帖隐藏的内容需要积分高于 {$creditsrequire} 才可浏览，您当前积分为 {$_G['member']['credits']}</div>
EOF;
?><?php return $return;?><?php }

function tpl_hide_credits($creditsrequire, $message) {?><?php
$return = <<<EOF
<div class="locked">以下内容需要积分高于 {$creditsrequire} 才可浏览</div>
{$message}<br /><br />

EOF;
?><?php return $return;?><?php }

function tpl_codedisp($code) {
$randomid = 'code_'.random(3);?><?php
$return = <<<EOF
<div class="blockcode"><div id="{$randomid}"><ol><li>{$code}</ol></div><em onclick="copycode($('{$randomid}'));">复制代码</em></div>
EOF;
?><?php return $return;?><?php }

function tpl_quote() {?><?php
$return = <<<EOF
<div class="quote"><blockquote>\\1</blockquote></div>
EOF;
?><?php return $return;?><?php }

function tpl_free() {?><?php
$return = <<<EOF
<div class="quote"><blockquote>\\1</blockquote></div>
EOF;
?><?php return $return;?><?php }

function tpl_hide_reply() {
global $_G;?><?php
$return = <<<EOF
<div class="showhide"><h4>本帖隐藏的内容</h4>\\1</div>

EOF;
?><?php return $return;?><?php }

function tpl_hide_reply_hidden() {
global $_G;?><?php
$return = <<<EOF
<div class="locked">
EOF;
 if($_G['uid']) { 
$return .= <<<EOF
{$_G['username']}
EOF;
 } else { 
$return .= <<<EOF
游客
EOF;
 } 
$return .= <<<EOF
，如果您要查看本帖隐藏内容请<a href="forum.php?mod=post&amp;action=reply&amp;fid={$_G['fid']}&amp;tid={$_G['tid']}" onclick="showWindow('reply', this.href)">回复</a></div>
EOF;
?><?php return $return;?><?php }

function attachlist($attach) {
global $_G;
$attach['refcheck'] = (!$attach['remote'] && $_G['setting']['attachrefcheck']) || ($attach['remote'] && ($_G['setting']['ftp']['hideurl'] || ($attach['isimage'] && $_G['setting']['attachimgpost'] && strtolower(substr($_G['setting']['ftp']['attachurl'], 0, 3)) == 'ftp')));
$aidencode = packaids($attach);
$widthcode = attachwidth($attach['width']);
$is_archive = $_G['forum_thread']['is_archived'] ? "&fid=".$_G['fid']."&archiveid=".$_G[forum_thread][archiveid] : '';?><?php
$return = <<<EOF

<ignore_js_op>
<dl class="tattl">
<dt>
{$attach['attachicon']}
</dt>
<dd>
<p class="attnm">

EOF;
 if(!$attach['price'] || $attach['payed']) { 
$return .= <<<EOF

<a href="forum.php?mod=attachment{$is_archive}&amp;aid={$aidencode}" onmouseover="showMenu({'ctrlid':this.id,'pos':'12'})" id="aid{$attach['aid']}" target="_blank">{$attach['filename']}</a>

EOF;
 } else { 
$return .= <<<EOF

<a href="forum.php?mod=misc&amp;action=attachpay&amp;aid={$attach['aid']}&amp;tid={$attach['tid']}" onclick="showWindow('attachpay', this.href)">{$attach['filename']}</a>

EOF;
 } 
$return .= <<<EOF

<div class="tip tip_4" id="aid{$attach['aid']}_menu" style="display: none">
<div class="tip_c">
<p class="y">{$attach['dateline']} 上传</p>
<p>下载次数: {$attach['downloads']}</p>

EOF;
 if(!$attach['attachimg'] && $_G['getattachcredits']) { 
$return .= <<<EOF
下载积分: {$_G['getattachcredits']}<br />
EOF;
 } 
$return .= <<<EOF

</div>
<div class="tip_horn"></div>
</div>
</p>
<p>{$attach['attachsize']}
EOF;
 if($attach['readperm']) { 
$return .= <<<EOF
, 阅读权限: <strong>{$attach['readperm']}</strong>
EOF;
 } 
$return .= <<<EOF
, 下载次数: {$attach['downloads']}
EOF;
 if(!$attach['attachimg'] && $_G['getattachcredits']) { 
$return .= <<<EOF
, 下载积分: {$_G['getattachcredits']}
EOF;
 } 
$return .= <<<EOF
</p>
<p>

EOF;
 if($attach['price']) { 
$return .= <<<EOF

售价: <strong>{$attach['price']} {$_G['setting']['extcredits'][$_G['setting']['creditstransextra']['1']]['unit']}{$_G['setting']['extcredits'][$_G['setting']['creditstransextra']['1']]['title']}</strong> &nbsp;[<a href="forum.php?mod=misc&amp;action=viewattachpayments&amp;aid={$attach['aid']}" onclick="showWindow('attachpay', this.href)" target="_blank">记录</a>]

EOF;
 if(!$attach['payed']) { 
$return .= <<<EOF

&nbsp;[<a href="forum.php?mod=misc&amp;action=attachpay&amp;aid={$attach['aid']}&amp;tid={$attach['tid']}" onclick="showWindow('attachpay', this.href)">购买</a>]

EOF;
 } } 
$return .= <<<EOF

</p>

EOF;
 if($attach['description']) { 
$return .= <<<EOF
<p class="xg2">{$attach['description']}</p>
EOF;
 } 
$return .= <<<EOF

</dd>
</dl>
</ignore_js_op>

EOF;
?><?php return $return;?><?php }

function imagelist($attach, $firstpost = 0) {
global $_G;
$attach['refcheck'] = (!$attach['remote'] && $_G['setting']['attachrefcheck']) || ($attach['remote'] && ($_G['setting']['ftp']['hideurl'] || ($attach['isimage'] && $_G['setting']['attachimgpost'] && strtolower(substr($_G['setting']['ftp']['attachurl'], 0, 3)) == 'ftp')));
$aidencode = packaids($attach);
$widthcode = attachwidth($attach['width']);
$is_archive = $_G['forum_thread']['is_archived'] ? "&fid=".$_G['fid']."&archiveid=".$_G[forum_thread][archiveid] : '';
$attachthumb = getimgthumbname($attach['attachment']);?><?php
$__STATICURL = STATICURL;$return = <<<EOF


EOF;
 if($attach['attachimg'] && $_G['setting']['showimages'] && ($_G['group']['allowgetimage'] || $_G['uid'] == $attach['uid'])) { 
$return .= <<<EOF

<ignore_js_op>

EOF;
 if(!IS_ROBOT) { 
$return .= <<<EOF

<dl class="tattl attm">
<dt></dt>
<dd>
<p class="mbn">
<a href="forum.php?mod=attachment{$is_archive}&amp;aid={$aidencode}&amp;nothumb=yes" onmouseover="showMenu({'ctrlid':this.id,'pos':'12'})" id="aid{$attach['aid']}" class="xw1" target="_blank">{$attach['filename']}</a>
<em class="xg1">({$attach['attachsize']}, 下载次数: {$attach['downloads']})</em>

EOF;
 if($firstpost && $_G['fid'] && $_G['forum']['picstyle'] && ($_G['forum']['ismoderator'] || $_G['uid'] == $attach['uid'])) { 
$return .= <<<EOF

<a href="forum.php?mod=ajax&amp;action=setthreadcover&amp;aid={$attach['aid']}&amp;fid={$_G['fid']}" onclick="showWindow('setcover{$attach['aid']}', this.href)">设为封面</a>

EOF;
 } 
$return .= <<<EOF

</p>
<div class="tip tip_4" id="aid{$attach['aid']}_menu" style="display: none">
<div class="tip_c">
<p class="y">{$attach['dateline']} 上传</p>
<p>下载次数: {$attach['downloads']}</p>
<p>
<a href="javascript:;" onclick="imageRotate('aimg_{$attach['aid']}', 1)"><img src="{$__STATICURL}image/common/rleft.gif" /></a>
<a href="javascript:;" onclick="imageRotate('aimg_{$attach['aid']}', 2)"><img src="{$__STATICURL}image/common/rright.gif" /></a>
</p>
</div>
<div class="tip_horn"></div>
</div>
<p class="mbn">

EOF;
 if($attach['readperm']) { 
$return .= <<<EOF
阅读权限: <strong>{$attach['readperm']}</strong>
EOF;
 } if($attach['price']) { 
$return .= <<<EOF
售价: <strong>{$attach['price']} {$_G['setting']['extcredits'][$_G['setting']['creditstransextra']['1']]['unit']}{$_G['setting']['extcredits'][$_G['setting']['creditstransextra']['1']]['title']}</strong> &nbsp;[<a href="forum.php?mod=misc&amp;action=viewattachpayments&amp;aid={$attach['aid']}" onclick="showWindow('attachpay', this.href)" target="_blank">记录</a>]

EOF;
 if(!$attach['payed']) { 
$return .= <<<EOF

&nbsp;[<a href="forum.php?mod=misc&amp;action=attachpay&amp;aid={$attach['aid']}&amp;tid={$attach['tid']}" onclick="showWindow('attachpay', this.href)" target="_blank">购买</a>]

EOF;
 } } 
$return .= <<<EOF

</p>

EOF;
 if($attach['description']) { 
$return .= <<<EOF
<p class="mbn xg2">{$attach['description']}</p>
EOF;
 } if(!$attach['price'] || $attach['payed']) { 
$return .= <<<EOF

<p class="mbn">

EOF;
 if($_G['setting']['thumbstatus'] && $attach['thumb']) { 
$return .= <<<EOF

<a href="javascript:;"><img id="aimg_{$attach['aid']}" src="{$__STATICURL}image/common/none.gif" onclick="zoom(this, this.getAttribute('zoomfile'))" zoomfile="
EOF;
 if($attach['refcheck']) { 
$return .= <<<EOF
forum.php?mod=attachment{$is_archive}&aid={$aidencode}&noupdate=yes&nothumb=yes
EOF;
 } else { 
$return .= <<<EOF
{$attach['url']}{$attach['attachment']}
EOF;
 } 
$return .= <<<EOF
" file="
EOF;
 if($attach['refcheck']) { 
$return .= <<<EOF
forum.php?mod=attachment{$is_archive}&aid={$aidencode}
EOF;
 } else { 
$return .= <<<EOF
{$attach['url']}{$attachthumb}
EOF;
 } 
$return .= <<<EOF
" alt="{$attach['imgalt']}" title="{$attach['imgalt']}" w="{$attach['width']}" /></a>

EOF;
 } else { 
$return .= <<<EOF

<img id="aimg_{$attach['aid']}" src="{$__STATICURL}image/common/none.gif" zoomfile="
EOF;
 if($attach['refcheck']) { 
$return .= <<<EOF
forum.php?mod=attachment{$is_archive}&aid={$aidencode}&noupdate=yes&nothumb=yes
EOF;
 } else { 
$return .= <<<EOF
{$attach['url']}{$attach['attachment']}
EOF;
 } 
$return .= <<<EOF
" file="
EOF;
 if($attach['refcheck']) { 
$return .= <<<EOF
forum.php?mod=attachment{$is_archive}&aid={$aidencode}&noupdate=yes
EOF;
 } else { 
$return .= <<<EOF
{$attach['url']}{$attach['attachment']}
EOF;
 } 
$return .= <<<EOF
" {$widthcode} id="aimg_{$attach['aid']}" alt="{$attach['imgalt']}" title="{$attach['imgalt']}" w="{$attach['width']}" />

EOF;
 } 
$return .= <<<EOF

</p>

EOF;
 } 
$return .= <<<EOF

</dd>
</dl>

EOF;
 } else { 
$return .= <<<EOF

<dl class="tattl attm">

EOF;
 if(!$attach['price'] || $attach['payed']) { 
$return .= <<<EOF

<dd>

EOF;
 if($attach['description']) { 
$return .= <<<EOF
<p>{$attach['description']}</p>
EOF;
 } 
$return .= <<<EOF

<img src="
EOF;
 if($attach['refcheck']) { 
$return .= <<<EOF
forum.php?mod=attachment{$is_archive}&aid={$aidencode}&noupdate=yes
EOF;
 } else { 
$return .= <<<EOF
{$attach['url']}{$attach['attachment']}
EOF;
 } 
$return .= <<<EOF
" alt="{$attach['imgalt']}" title="{$attach['imgalt']}" />
</dd>

EOF;
 } 
$return .= <<<EOF

</dl>

EOF;
 } 
$return .= <<<EOF

</ignore_js_op>

EOF;
 } 
$return .= <<<EOF


EOF;
?><?php return $return;?><?php }

function attachinpost($attach, $firstpost = 0) {
global $_G;
$attach['refcheck'] = (!$attach['remote'] && $_G['setting']['attachrefcheck']) || ($attach['remote'] && ($_G['setting']['ftp']['hideurl'] || ($attach['isimage'] && $_G['setting']['attachimgpost'] && strtolower(substr($_G['setting']['ftp']['attachurl'], 0, 3)) == 'ftp')));
$aidencode = packaids($attach);
$widthcode = attachwidth($attach['width']);
$is_archive = $_G['forum_thread']['is_archived'] ? '&fid='.$_G['fid'].'&archiveid='.$_G[forum_thread][archiveid] : '';
$attachthumb = getimgthumbname($attach['attachment']);
$replayinfo_g = get_replayinfo($attach['aid']);
$time_len = get_game_lentime($replayinfo_g['length']);
$data = get_replayplayerinfo($attach['aid']);
//echo $attach['aid'];
//print_r($attach);?><?php
$return = <<<EOF

<ignore_js_op>
<style type="text/css" id="replay_css">
.wa .wa_text {
 font-family: tahoma;
 font-size: 42px;
 font-weight:bold;
 position: absolute;
margin: 60px 0px 0px 50px;
}
.page
{
background: #E5ECF4;
color: #000000;
}
.tborder
{
background: #D1D1E1;
color: #000000;
border: 1px solid #0B198C;
}
.tcat
{
background: #869BBF url(../../images/gradients/gradient_tcat.gif) repeat-x top left;
color: #FFFFFF;
font: bold 10pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.tcat a:link, .tcat_alink
{
color: #ffffff;
text-decoration: none;
}
.tcat a:visited, .tcat_avisited
{
color: #ffffff;
text-decoration: none;
}
.tcat a:hover, .tcat a:active, .tcat_ahover
{
color: #FFFF66;
text-decoration: underline;
}
.thead
{
background: #5C7099 url(../../images/gradients/gradient_thead.gif) repeat-x top left;
color: #FFFFFF;
font: bold 12px tahoma, verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.thead a:link, .thead_alink
{
color: #FFFFFF;
}
.thead a:visited, .thead_avisited
{
color: #FFFFFF;
}
.thead a:hover, .thead a:active, .thead_ahover
{
color: #FFFF00;
}
.tfoot
{
background: #3E5C92;
color: #E0E0F6;
}
.tfoot a:link, .tfoot_alink
{
color: #E0E0F6;
}
.tfoot a:visited, .tfoot_avisited
{
color: #E0E0F6;
}
.tfoot a:hover, .tfoot a:active, .tfoot_ahover
{
color: #FFFF66;
}
.alt1, .alt1Active
{
background: #F5F5FF;
color: #000000;
}
.alt2, .alt2Active
{
background: #E5ECF4;
color: #000000;
}
.inlinemod
{
background: #FFFFCC;
color: #000000;
}
.wysiwyg
{
background: #F5F5FF;
color: #000000;
font: 10pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
margin: 5px 10px 10px 10px;
padding: 0px;
}
.wysiwyg a:link, .wysiwyg_alink
{
color: #22229C;
}
.wysiwyg a:visited, .wysiwyg_avisited
{
color: #22229C;
}
.wysiwyg a:hover, .wysiwyg a:active, .wysiwyg_ahover
{
color: #FF4400;
}
textarea, .bginput
{
font: 10pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.bginput option, .bginput optgroup
{
font-size: 10pt;
font-family: verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.button
{
font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}

option, optgroup
{
font-size: 12px;
font-family: verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.smallfont
{
font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.time
{
color: #666686;
}
.navbar
{
font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.highlight
{
color: #FF0000;
font-weight: bold;
}
.fjsel
{
background: #3E5C92;
color: #E0E0F6;
}
.fjdpth0
{
background: #F7F7F7;
color: #000000;
}
.panel
{
background: #E4E7F5 url(../../images/gradients/gradient_panel.gif) repeat-x top left;
color: #000000;
padding: 10px;
border: 2px outset;
}
.panelsurround
{
background: #D1D4E0 url(../../images/gradients/gradient_panelsurround.gif) repeat-x top left;
color: #000000;
}
legend
{
color: #22229C;
font: 12px tahoma, verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.vbmenu_control
{
background: #738FBF;
color: #FFFFFF;
font: bold 12px tahoma, verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
padding: 3px 6px 3px 6px;
white-space: nowrap;
}
.vbmenu_control a:link, .vbmenu_control_alink
{
color: #FFFFFF;
text-decoration: none;
}
.vbmenu_control a:visited, .vbmenu_control_avisited
{
color: #FFFFFF;
text-decoration: none;
}
.vbmenu_control a:hover, .vbmenu_control a:active, .vbmenu_control_ahover
{
color: #FFFFFF;
text-decoration: underline;
}
.vbmenu_popup

{
background: #FFFFFF;
color: #000000;
border: 1px solid #0B198C;
}
.vbmenu_option
{
background: #BBC7CE;
color: #000000;
font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
white-space: nowrap;
cursor: pointer;
}
.vbmenu_option a:link, .vbmenu_option_alink
{
color: #22229C;
text-decoration: none;
}
.vbmenu_option a:visited, .vbmenu_option_avisited
{
color: #22229C;
text-decoration: none;
}
.vbmenu_option a:hover, .vbmenu_option a:active, .vbmenu_option_ahover
{
color: #FFFFFF;
text-decoration: none;
}
.vbmenu_hilite
{
background: #8A949E;
color: #FFFFFF;
font: 12px verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
white-space: nowrap;
cursor: pointer;
}
.vbmenu_hilite a:link, .vbmenu_hilite_alink
{
color: #FFFFFF;
text-decoration: none;
}
.vbmenu_hilite a:visited, .vbmenu_hilite_avisited
{
color: #FFFFFF;
text-decoration: none;
}
.vbmenu_hilite a:hover, .vbmenu_hilite a:active, .vbmenu_hilite_ahover
{
color: #FFFFFF;
text-decoration: none;
}
/* ***** styling for 'big' usernames on postbit etc. ***** */
.bigusername { font-size: 14pt; }

/* ***** small padding on 'thead' elements ***** */
td.thead, th.thead, div.thead { padding: 4px; }

/* ***** basic styles for multi-page nav elements */
.pagenav a { text-decoration: none; }
.pagenav td { padding: 2px 4px 2px 4px; }

/* ***** de-emphasized text */
.shade, a.shade:link, a.shade:visited { color: #777777; text-decoration: none; }
a.shade:active, a.shade:hover { color: #FF4400; text-decoration: underline; }
.tcat .shade, .thead .shade, .tfoot .shade { color: #DDDDDD; }

/* ***** define margin and font-size for elements inside panels ***** */
.fieldset { margin-bottom: 6px; }
.fieldset, .fieldset td, .fieldset p, .fieldset li { font-size: 12px; }

.copyright { 
display: none; 
}
@charset "utf-8";

/* clear around */
.clearer {
clear:both;
}
.header {
margin-left:-10px;
margin-right:-10px;
width:600px;
height:35px;
color:#000033;
/*border-bottom:solid 1px #696256;*/
}
.header .howto_button {
height:25px;
width:25px;
position: absolute;
z-index: 1;
margin: 2px 0px 0px 560px;
}
.header .gameicon {
height:25px;
width:25px;
position: absolute;
z-index: 1;
margin: 5px 0px 0px 12px;
}

.section {
border:1px solid #8d7e64;
/*	background-color: #2f3237;/*#0c1e34;/*#51493c;*/
/*	color:#fff;*/
background-image:url(../../images/replays/replay_details.jpg);
color:#000033;
float:none;
width:764px;
padding:10px;
text-align:left;
position:relative;
}
/* Replay Details */
#replay_details {
padding-top:0px;
padding-bottom:10;
width:580px;
}
#replay_details .column {
float:left;
width:136px;
padding-top:12px;
}
#replay_details .map a {
display:block;
}
#replay_details .map img {
margin-bottom:5px;
width:120px;
height:120px;
}
#replay_details .columnplayer {
float:left;
width:300px;
padding-top:12px;
}
#replay_details .versus {
/*	background-color:#6699FF;/*#091626;*/
color:#FF0000;
text-align:center;
font-size:9pt;
/*	font-style:italic;*/
font-weight:bold;
height:16px;
border-bottom:dotted 1px #696256;
padding:2px 0px;
}

#replay_details .download_button {
height:25px;
width:126px;
margin:8px 0px 0px 3px;
padding:0px;
overflow:hidden;
}

#replay_details .commend_button {
height:65px;
width:98px;
margin:60px 12px 0px;
padding:0px;
overflow:hidden;
position:absolute;
bottom:30px;
right:0px;
}
#replay_details .commend_amount {
/*	font-family:Tohoma, Arial, Verdana, Helvetica, sans-serif;*/
font-size:36px;
line-height:10px;
padding: 16px 0px 0px 0px;
}
#replay_details .commend_name {
/*	font-family: Tohoma, Arial, Verdana, Helvetica, sans-serif;*/
font-size:14px;
line-height:6px;
padding: 0px 0px 2px 0px;
}
.other_details {
float:left;
list-style-type:none;
font-size:9pt;
/*	font-family: Tohoma, Arial, Verdana, Helvetica, sans-serif;*/
color:#8b837a;
margin:5px 0 0 5px;
padding:0;
}
.other_details li {
font-family:Tohoma, Arial, Verdana, Helvetica, sans-serif;
font-size:9pt;
}
.other_details li h5 {
font-size:9pt;
color: #000033;/*#fff;*/
display:inline;
margin-right:3px;
font-weight:bold;
}
#replay_details .footer a:hover {
color: #ff00ff;
}
#replay_details .map {
/*background-color:#282a2e;*//*#091626;*/
/*border:solid 1px #696256;*/
color:#8b837a;
text-align:center;
width:120px;
padding:5px;
}
.teams {
float:left;
width:250px;
margin-left:25px;
         padding-top:15px;
}
.team_players {
list-style-type:none;
vertical-align:middle;
padding:0px;
margin:0px;
}
.team_players li {
font-size:9pt;
border-bottom:dotted 1px #696256;
text-align:left;
padding-left:0px;
height:29px;
margin-top:4px;
         color:#000044
}
.team_players img {
vertical-align:middle;
width:31px;
height:25px;
padding: 1px;
border: #696256 1px solid;
}
.team_players span {
border:solid 0px #666666;
border-left-width:3px;
margin-left:2px;
padding-left: 2px;
}

#features_awards {
float: left;
z-index: 1;
}
.awards, .features {
height: 50px;
width:180px;
margin-left:12px;
margin-right:13px;
padding-left:20px;
}
.awards img, .features img {
padding:1px;
}
/*These footer classes are used when a box needs a footer, eg the replay details box*/
.footer {
/*background-color:#282a2e;*//*#091626;*/
margin-left:-10px;
margin-right:-10px;
margin-top:15px;
padding-top:2px;
width:580px;
height:18px;
color:#000033;
text-align:center;
filter: progid:dximagetransform.microsoft.gradient(gradienttype=1, startcolorstr=#c3daf5, endcolorstr=#ffffff);
}
.footer a, .footer a:active, .footer a:visited, .footer a:hover {
font-family: Tohoma, Arial, Verdana, Helvetica, sans-serif;
font-weight:normal;
font-size:9pt;
margin-right:5px;
margin-left:10px;
color:#000033;
}
.footer .datetime {
float:left;
padding-left:10px;
}
.footer .version {
float:right;
padding-right:10px;
}
.frame {
font-family: Tohoma, Arial, Verdana, Helvetica, sans-serif;
font-size: 9pt;
width:680px;
margin-bottom:10px;
margin-left:3%;
margin-right:3%;
}
.frame .frametop {
background-image:url(../../frame_t.gif);
height:12px;
font-size:0px;
}
.frame .framebody {
padding:0px 10px;
border:solid 1px #DEDFDE;
border-top-width:0px;
border-bottom-width:0px;
}
.frame .framebot {
clear:both;
background-image:url(../../frame_b.gif);
height:12px;
font-size:0px;
}
/* css button */
.commend_button_mouseout, .commend_button_mouseover, .commend_button_mousedown, .commend_button_mouseup, .download_button_mouseout, .download_button_mouseover, .download_button_mousedown, .download_button_mouseup, .howto_button_mouseout, .howto_button_mouseover, .howto_button_mousedown, .howto_button_mouseup, .wa_button_mouseout, .wa_button_mouseover, .wa_button_mousedown, .wa_button_mouseup  {
cursor:pointer;
color: #000033;
font-size: 12px;
padding-top: 0px;
padding-left: 0px;
padding-right: 0px;
height:65px;
width: 98px;
}
.download_button_mouseout, .download_button_mouseover, .download_button_mousedown, .download_button_mouseup {
height:25px;
width:126px;
font-size:14px;
/*	font-weight:bold;*/
text-align:center;
padding-top:1px;
}
.howto_button_mouseout, .howto_button_mouseover, .howto_button_mousedown, .howto_button_mouseup {
height:30px;
width:30px;
font-size:22px;
font-weight:bold;
text-align:center;
}

.wa_button_mouseout, .wa_button_mouseover, .wa_button_mousedown, .wa_button_mouseup {
     height:20px;
width: 150px;
font-size:22px;
font-weight:bold;
text-align:center;
}

.commend_button_mouseout, .commend_button_mouseover, .commend_button_mouseup, .download_button_mouseout, .download_button_mouseover, .download_button_mouseup, .howto_button_mouseout, .howto_button_mouseover, .howto_button_mouseup, .wa_button_mouseout, .wa_button_mouseover, .wa_button_mouseup {
border-left: #ffffff 0px solid;
border-right: #ffffff 0px solid;
border-top: #ffffff 0px solid;
border-bottom: #ffffff 0px solid;
}
.commend_button_mouseout, .commend_button_mouseup, .download_button_mouseout, .download_button_mouseup, .howto_button_mouseout, .howto_button_mouseup, .wa_button_mouseout, .wa_button_mouseup  {
filter: progid:dximagetransform.microsoft.gradient(gradienttype=0, startcolorstr=#ffffff, endcolorstr=#c3daf5);
}
.commend_button_mouseup, .download_button_mouseup, .howto_button_mouseup, .wa_button_mouseup {
color: blue;
}
.commend_button_mouseover, .download_button_mouseover, .howto_button_mouseover, .wa_button_mouseover {
color: blue;
filter: progid:dximagetransform.microsoft.gradient(gradienttype=0, startcolorstr=#ffffff, endcolorstr=#d7e7fa);
}
.commend_button_mousedown, .download_button_mousedown, .howto_button_mousedown, .wa_button_mousedown {
/*border-left: #ffe400 1px solid;
border-right: #ffe400 1px solid;
border-top: #ffe400 1px solid;
border-bottom: #ffe400 1px solid;*/
color: red;
filter: progid:dximagetransform.microsoft.gradient(gradienttype=0, startcolorstr=#ffffff, endcolorstr=#c3daf5);
}/* CSS Document */

ul{
list-style:none;
margin: 0px;
}
</style>

EOF;
 if($attach['attachimg'] && $_G['setting']['showimages'] && (!$attach['price'] || $attach['payed']) && ($_G['group']['allowgetimage'] || $_G['uid'] == $attach['uid'])) { if(!IS_ROBOT) { 
$return .= <<<EOF

<img src="
EOF;
 if($attach['refcheck']) { 
$return .= <<<EOF
forum.php?mod=attachment{$is_archive}&aid={$aidencode}&noupdate=yes
EOF;
 } else { 
$return .= <<<EOF
{$attach['url']}{$attach['attachment']}
EOF;
 } 
$return .= <<<EOF
" alt="{$attach['imgalt']}" title="{$attach['imgalt']}" />	

EOF;
 } else { 
$return .= <<<EOF

<img src="
EOF;
 if($attach['refcheck']) { 
$return .= <<<EOF
forum.php?mod=attachment{$is_archive}&aid={$aidencode}&noupdate=yes
EOF;
 } else { 
$return .= <<<EOF
{$attach['url']}{$attach['attachment']}
EOF;
 } 
$return .= <<<EOF
" alt="{$attach['imgalt']}" title="{$attach['imgalt']}" />

EOF;
 } } else { if(!$attach['price'] || $attach['payed']) { 
$return .= <<<EOF





<div >
<div class="frametop"></div>
<div class="framebody">

<div class="section" id="replay_details">
<div class="header" >
<div class="gameicon" ><img class="thisicon" src="./static/image/replays/{$replayinfo_g['gametype']}.gif" width="25" height="25"/></div>
<div class="howto_button">
<button class="howto_button_mouseout" onmouseover="this.className='howto_button_mouseover'" onmouseout="this.className='howto_button_mouseout'" onmousedown="this.className='howto_button_mousedown'" onmouseup="this.className='howto_button_mouseup'" onclick="javascript:window.open('http://www.gametotal.org/forum/showthread.php?t=21549');" title="怎么观看游戏录像?"><img class="inlineimg" src="./static/image/replays/{$replayinfo_g['gametype']}.gif" alt="ra3replay" title="怎么观看游戏录像?" width="25" height="25" border="0" style="vertical-align:baseline" /></button>				</div>
</div>
<div class="column">
<div class="map"><img title="{$replayinfo_g['mapname']}" alt="{$replayinfo_g['mapname']}" src="{$data['mapimage']}" /><br />{$replayinfo_g['mapdispname']}</div>
<div class="download_button">
<a href="forum.php?mod=attachment{$is_archive}&amp;aid={$aidencode}" >
<img src="./static/image/replays/download_replay.jpg" border=0>
</a>
</div>
<ul class="other_details">
<li>
下载次数：{$attach['downloads']}
</li>
<li>
录像大小:{$attach['attachsize']}
</li> 
</ul>

</div>
<div class="teams">{$data['player']}</div> 			
<div class="wa">	
<div class="wa_text"></div>
</div>			
<div id="features_awards"></div>
<div class="clearer"></div>
<div class="footer">
<span class="datetime">录像时间：[<strong>{$replayinfo_g['time']}</strong> <strong>|</strong> <strong>{$time_len}</strong>]</span>
<span class="version">录像版本：[<strong>{$replayinfo_g['version']}</strong>]</span>
</div>
</div>
</div>
<div class="framebot"></div>
</div>


EOF;
 } else { 
$return .= <<<EOF

<!--a href="forum.php?mod=misc&amp;action=attachpay&amp;aid={$attach['aid']}&amp;tid={$attach['tid']}" onclick="showWindow('attachpay', this.href)">{$attach['filename']}</a-->

EOF;
 } } 
$return .= <<<EOF


</ignore_js_op>

EOF;
?><?php return $return;?><?php }?>