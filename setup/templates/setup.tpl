<!doctype html>
<html>
<head>
<meta charset="utf-8" />
<title>{TITLE}</title>
<link rel="shortcut icon" href="images/icons/favicon.ico" />
<link href="style/setup.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
//<![CDATA[
	isMSIE = (navigator.appName == "Microsoft Internet Explorer");
	isMSIE5 = isMSIE && (navigator.userAgent.indexOf('MSIE 5') != -1);
	isMSIE5_0 = isMSIE && (navigator.userAgent.indexOf('MSIE 5.0') != -1);

	if (navigator.userAgent.indexOf('Opera') != -1) {
		isMSIE = false;
	}
	
	function IEAlphaInit(obj) {
		if (isMSIE && !obj.IEswapped) {
            obj.IEswapped = true;
            obj.src = 'images/spacer.gif';
        }
	}
	
	function IEAlphaApply(obj, img) {
		if (isMSIE) {
            obj.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + img + "');"
        } else {
            obj.src=img;
        }
	}
	
	function clickHandler(obj) {
		if (obj.clicked) {
            obj.clicked = false;
        } else {
            obj.clicked = true
        }
		if (obj.clicked) { 
			if (obj.mouseIn) {
				IEAlphaApply(obj, obj.clickimgover);
			} else {
				IEAlphaApply(obj, obj.clickimgnormal);
			}
		} else {
			if (obj.mouseIn) {
				IEAlphaApply(obj, obj.imgover);
			} else {
				IEAlphaApply(obj, obj.imgnormal);
			}
		}
	}
	
	function mouseoverHandler(obj) {
		obj.mouseIn = true;
		
		if (obj.clicked) {
			IEAlphaApply(obj, obj.clickimgover);
		} else {
			IEAlphaApply(obj, obj.imgover);
		}
	}
	
	function mouseoutHandler(obj) {
		obj.mouseIn = false;
		
		if (obj.clicked) {
			IEAlphaApply(obj, obj.clickimgnormal);
		} else {
			IEAlphaApply(obj, obj.imgnormal);
		}
	}
	
	function showHideMessage(obj, div) {
		if (!obj.clicked) {
			div.className = 'entry_open';
		} else {
			div.className = 'entry_closed';
		}
	}
//]]>
</script>
</head>
<body>
    <div class="floater"></div>
    <div class="content">
        <form name="setupform" method="post" action="index.php">
            <input type="hidden" name="step" value="" />
            <div id="setupBox">
                <div id="setupHead">
                    <img src="images/drugCMS-Logo.png" alt="drugCMS" />
                    <div id="Info">
                        {INFO}
                    </div>
                </div>
                <div id="setupHeadlinePath">
                    <div style="float: left;">{HEADER}</div>
                    <div style="float: right;">{STEPS}</div>
                </div>
                <div id="setupBody">
                    {CONTENT}
                    <div id="setupFootnote">
                        &copy; 2013-2015 <a href="http://www.spider-it.de" target="_blank">Spider IT Deutschland</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>