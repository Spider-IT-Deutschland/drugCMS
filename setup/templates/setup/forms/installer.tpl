<h1>{TITLE}</h1>
<div id="installing">{DESCRIPTION}</div>
<div id="installingdone" style="visibility:hidden;">{DONEINSTALLATION}</div>
<div style="margin-top: 20px; width: 726px; overflow: hidden; background-color: #fff; border: 1px solid #999999; border-radius: 13px;">
    <div style="width: 724px; overflow: hidden; background-color: #fff; border: 1px solid white; border-radius: 13px;">
        <div id="progressbar" style="padding: 0px; width: 0px; height: 16px; background-color: #69B52D; background-image: url('images/controls/ProgressBar.png'); background-position: 0px 0px; border-radius: 13px;"></div>
    </div>
</div>
<script type="text/javascript">
//<![CDATA[
    var elm = document.getElementById("progressbar");
    var left = 0;
    function updateProgressbar (percent) { 
        width = ((724 / 100) * percent);
        elm.style.width = width+"px";
    }
    function moveBarBackground() {
        left += 3;
        if (left >= 1000) {
            left = 0;
        }
        elm.style.backgroundPosition = left + 'px 0px';
    }
    var tmr = setInterval('moveBarBackground()', 10);
//]]>
</script>
<iframe style="display: none; width: 500px; height: 100px;" src="{DBUPDATESCRIPT}"></iframe>
<div id="next" style="display: none; position: absolute; right: 10px; bottom: 8px;">{BACK}{NEXT}</div>