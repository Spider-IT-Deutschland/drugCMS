<input type="hidden" name="setuptype" value="{SETUPTYPE}">
<div style="margin-bottom: 20px;">
    <h1>{OPTION_SETUP}</h1>
    {DESCRIPTION_SETUP}
</div>
<div style="margin-bottom: 20px;">
    <h1>{OPTION_UPGRADE}</h1>
    {DESCRIPTION_UPGRADE}
</div>
<div style="margin-bottom: 20px;">
    <h1>{OPTION_MIGRATION}</h1>
    {DESCRIPTION_MIGRATION}
</div>
<div style="position: absolute; right: 10px; bottom: 8px;">{BACK}<a id="m17" onclick="document.setupform.submit();" href="#"><img id="m18" class="button" style="border: 0px none; progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../drugcms/images/submit.gif');" onmouseout="if (isMSIE) { this.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'../drugcms/images/submit.gif\');'; } else { this.src='../drugcms/images/submit.gif'; }" onmouseover="if (isMSIE) { this.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'../drugcms/images/submit_hover.gif\');'; } else { this.src='../drugcms/images/submit_hover.gif'; }" onload="this.imgnormal = '../drugcms/images/submit.gif'; this.imgover = '../drugcms/images/submit_hover.gif'; this.clickimgnormal = ''; this.clickimgover = ''; if (!this.init) {IEAlphaInit(this); IEAlphaApply(this, this.imgnormal); this.init = true;}" src="../drugcms/images/submit.gif" alt=""></a></div>
<script type="text/javascript">
//<![CDATA[
    // Only changes on clicking the option, must be set on coming back
    // when the option is still checked
    document.setupform.step.value = document.setupform.setuptype.value + '1';
//]]>
</script>