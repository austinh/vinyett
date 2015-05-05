var upload_uploading = 0;
function upload_is_safari() {
    if (navigator.userAgent.indexOf("Safari") != -1) {
        return 1
    }
    return 0
}
function upload_is_moz_one() {
    if (navigator.userAgent.indexOf("Mozilla/5.0") != -1 && navigator.userAgent.indexOf("rv:1.0") != -1) {
        return 1
    }
    return 0
}
function upload_click_button() {
    if (upload_is_safari()) {
        upload_hide_form();
        document.getElementById("upload_form").submit()
    }
    upload_uploading = 1;
    return true
}
function upload_submit_form() {
    if (!upload_is_safari() && !upload_is_moz_one()) {
        upload_hide_form()
    }
    upload_uploading = 1;
    return true
}
function upload_hide_form() {
    document.getElementById("upload_form_container").style.display = "none";
    document.getElementById("upload_uploading_container").style.display = "block"
}
function createYUIUploader() {
    writeDebug("Extending YUI flash/upload components..");
    /*
	 * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
	 *
	 * SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License:
	 * http://www.opensource.org/licenses/mit-license.php
	 */
    if (!document.getElementById && document.all) {
        document.getElementById = function(id) {
            return document.all[id]
        }
    }
    var getQueryParamValue = deconcept.util.getRequestParameter;
    var FlashObject = deconcept.SWFObject;
    var SWFObject = deconcept.SWFObject;
    YAHOO.widget.FlashAdapter = function(swfURL, containerID, attributes, buttonSkin) {
        this._queue = this._queue || [];
        this._events = this._events || {};
        this._configs = this._configs || {};
        attributes = attributes || {};
        this._id = attributes.id = attributes.id || YAHOO.util.Dom.generateId(null, "yuigen");
        attributes.version = attributes.version || "9.0.45";
        attributes.backgroundColor = attributes.backgroundColor || "#ffffff";
        this._attributes = attributes;
        this._swfURL = swfURL;
        this._containerID = containerID;
        this._embedSWF(this._swfURL, this._containerID, attributes.id, attributes.version, attributes.backgroundColor, attributes.expressInstall, attributes.wmode, buttonSkin);
        try {
            this.createEvent("contentReady")
        } catch(e) {}
    };
    YAHOO.extend(YAHOO.widget.FlashAdapter, YAHOO.util.AttributeProvider, {
        _swfURL: null,
        _containerID: null,
        _swf: null,
        _id: null,
        _initialized: false,
        _attributes: null,
        toString: function() {
            return "FlashAdapter " + this._id
        },
        destroy: function() {
            if (this._swf) {
                var container = YAHOO.util.Dom.get(this._containerID);
                container.removeChild(this._swf)
            }
            var instanceName = this._id;
            for (var prop in this) {
                if (YAHOO.lang.hasOwnProperty(this, prop)) {
                    this[prop] = null
                }
            }
        },
        _embedSWF: function(swfURL, containerID, swfID, version, backgroundColor, expressInstall, wmode, buttonSkin) {
            var swfObj = new deconcept.SWFObject(swfURL, swfID, "100%", "100%", version, backgroundColor);
            if (expressInstall) {
                swfObj.useExpressInstall(expressInstall)
            }
            swfObj.addParam("allowScriptAccess", "always");
            if (wmode) {
                swfObj.addParam("wmode", wmode)
            }
            swfObj.addParam("menu", "false");
            swfObj.addVariable("allowedDomain", document.location.hostname);
            swfObj.addVariable("elementID", swfID);
            swfObj.addVariable("eventHandler", "YAHOO.widget.FlashAdapter.eventHandler");
            if (buttonSkin) {
                swfObj.addVariable("buttonSkin", buttonSkin)
            }
            var container = YAHOO.util.Dom.get(containerID);
            var result = swfObj.write(container);
            if (result) {
                this._swf = YAHOO.util.Dom.get(swfID);
                window._swfOwner = this
            } else {}
        },
        _eventHandler: function(event) {
            var type = event.type;
            switch (type) {
            case "swfReady":
                this._loadHandler();
                return;
            case "log":
                return
            }
            this.fireEvent(type, event)
        },
        _loadHandler: function() {
            this._initialized = false;
            this._initAttributes(this._attributes);
            this.setAttributes(this._attributes, true);
            this._initialized = true;
            this.fireEvent("contentReady")
        },
        set: function(name, value) {
            this._attributes[name] = value;
            YAHOO.widget.FlashAdapter.superclass.set.call(this, name, value)
        },
        _initAttributes: function(attributes) {
            this.getAttributeConfig("altText", {
                method: this._getAltText
            });
            this.setAttributeConfig("altText", {
                method: this._setAltText
            });
            this.getAttributeConfig("swfURL", {
                method: this._getSWFURL
            })
        },
        _getSWFURL: function() {
            return this._swfURL
        },
        _getAltText: function() {
            return this._swf.getAltText()
        },
        _setAltText: function(value) {
            return this._swf.setAltText(value)
        }
    });
    YAHOO.widget.FlashAdapter.eventHandler = function(elementID, event) {
        var loadedSWF = YAHOO.util.Dom.get(elementID);
        if (!window._swfOwner) {
            setTimeout(function() {
                YAHOO.widget.FlashAdapter.eventHandler(elementID, event)
            },
            0)
        } else {
            window._swfOwner._eventHandler(event)
        }
    };
    YAHOO.widget.FlashAdapter.proxyFunctionCount = 0;
    YAHOO.widget.FlashAdapter.createProxyFunction = function(func) {
        var index = YAHOO.widget.FlashAdapter.proxyFunctionCount;
        YAHOO.widget.FlashAdapter["proxyFunction" + index] = function() {
            return func.apply(null, arguments)
        };
        YAHOO.widget.FlashAdapter.proxyFunctionCount++;
        return "YAHOO.widget.FlashAdapter.proxyFunction" + index.toString()
    };
    YAHOO.widget.FlashAdapter.removeProxyFunction = function(funcName) {
        if (!funcName || funcName.indexOf("YAHOO.widget.FlashAdapter.proxyFunction") < 0) {
            return
        }
        funcName = funcName.substr(26);
        YAHOO.widget.FlashAdapter[funcName] = null
    };
    YAHOO.widget.Uploader = function(containerId, buttonSkin) {
        var newWMode = "window";
        if (! (buttonSkin)) {
            newWMode = "transparent"
        }
        YAHOO.widget.Uploader.superclass.constructor.call(this, YAHOO.widget.Uploader.SWFURL, containerId, {
            wmode: newWMode
        },
        buttonSkin);
        this.createEvent("mouseDown");
        this.createEvent("mouseUp");
        this.createEvent("rollOver");
        this.createEvent("rollOut");
        this.createEvent("click");
        this.createEvent("fileSelect");
        this.createEvent("uploadStart");
        this.createEvent("uploadProgress");
        this.createEvent("uploadCancel");
        this.createEvent("uploadComplete");
        this.createEvent("uploadCompleteData");
        this.createEvent("uploadError")
    };
    YAHOO.widget.Uploader.SWFURL = "/flu1/yuploadcomponent.swf";
    YAHOO.extend(YAHOO.widget.Uploader, YAHOO.widget.FlashAdapter, {
        upload: function(fileID, uploadScriptPath, method, vars, fieldName) {
            this._swf.upload(fileID, uploadScriptPath, method, vars, fieldName)
        },
        uploadAll: function(uploadScriptPath, method, vars, fieldName) {
            this._swf.uploadAll(uploadScriptPath, method, vars, fieldName)
        },
        cancel: function(fileID) {
            this._swf.cancel(fileID)
        },
        clearFileList: function() {
            this._swf.clearFileList()
        },
        removeFile: function(fileID) {
            this._swf.removeFile(fileID)
        },
        setAllowLogging: function(allowLogging) {
            this._swf.setAllowLogging(allowLogging)
        },
        setSimUploadLimit: function(simUploadLimit) {
            this._swf.setSimUploadLimit(simUploadLimit)
        },
        setAllowMultipleFiles: function(allowMultipleFiles) {
            this._swf.setAllowMultipleFiles(allowMultipleFiles)
        },
        setFileFilters: function(fileFilters) {
            this._swf.setFileFilters(fileFilters)
        },
        enable: function() {
            this._swf.enable()
        },
        disable: function() {
            this._swf.disable()
        }
    });
    YAHOO.register("uploader", YAHOO.widget.Uploader, {
        version: "2.6.0",
        build: "1321"
    })
}
function flashError() {}
var escape_utf8_bytes = function(E) {
    if (E === "" || E === null || E === undefined) {
        return ""
    }
    E = E.toString();
    var A = "";
    for (var D = 0; D < E.length; D++) {
        var G = E.charCodeAt(D);
        var C = new Array();
        if (G > 65536) {
            C[0] = 240 | ((G & 1835008) >>> 18);
            C[1] = 128 | ((G & 258048) >>> 12);
            C[2] = 128 | ((G & 4032) >>> 6);
            C[3] = 128 | (G & 63)
        } else {
            if (G > 2048) {
                C[0] = 224 | ((G & 61440) >>> 12);
                C[1] = 128 | ((G & 4032) >>> 6);
                C[2] = 128 | (G & 63)
            } else {
                if (G > 128) {
                    C[0] = 192 | ((G & 1984) >>> 6);
                    C[1] = 128 | (G & 63)
                } else {
                    C[0] = G
                }
            }
        }
        if (C.length > 1) {
            for (var B = 0; B < C.length; B++) {
                A += (String.fromCharCode(C[B]))
            }
        } else {
            A += (String.fromCharCode(C[0]))
        }
    }
    return A;
};
var upPage = new function() {
    this.toggle = function() {
        row = _ge("moderation");
        if (Y.D.hasClass(row, "open")) {
            Y.D.removeClass(row, "open")
        } else {
            Y.D.addClass(row, "open")
        }
        return false
    }
};
function goOldSkool(B) {
    var C = "?";
    var A = null;
    for (A in B) {
        if (B.hasOwnProperty(A) && A != "result") {
            C += (A + "=" + encodeURI(B[A]) + "&")
        }
    }
    enableChoosePhotos();
    C = C.substr(0, C.length - 1);
    window.location.replace(_ge("upload-add-files").href + C)
}
if (typeof YAHOO.util.BgPosAnim == "undefined") { (function() {
        YAHOO.util.BgPosAnim = function(E, D, G, H) {
            YAHOO.util.BgPosAnim.superclass.constructor.apply(this, arguments)
        };
        YAHOO.extend(YAHOO.util.BgPosAnim, YAHOO.util.Anim);
        var B = YAHOO.util;
        var C = B.BgPosAnim.superclass;
        var A = B.BgPosAnim.prototype;
        A.toString = function() {
            var D = this.getEl();
            var E = D.id || D.tagName;
            return ("BgPosAnim " + E)
        };
        A.patterns.bgPos = /^backgroundPosition|background-position$/i;
        A.setAttribute = function(D, G, E) {
            if (this.patterns.bgPos.test(D)) {
                G = G[0] + E[0] + " " + G[1] + E[1];
                YAHOO.util.Dom.setStyle(this.getEl(), D, G)
            } else {
                C.setAttribute.apply(this, arguments)
            }
        },
        A.doMethod = function(E, J, G) {
            var I;
            if (this.patterns.bgPos.test(E)) {
                I = [];
                for (var H = 0,
                D = J.length; H < D; ++H) {
                    I[H] = C.doMethod.call(this, E, J[H], G[H])
                }
            } else {
                I = C.doMethod.apply(this, arguments)
            }
            return I
        }
    })()
}
F.format_file_size = function(A, D, J) {
    var J = (typeof J != "undefined" ? J.toLowerCase() : "bytes");
    var D = (typeof D != "undefined" ? D.toLowerCase() : null);
    var E = {
        bytes: {
            string: F.output.format_strs.uploadr_filesize_bytes,
            scale: 1,
            decimalPlaces: 0
        },
        kb: {
            string: F.output.format_strs.uploadr_filesize_kb,
            scale: 1024,
            decimalPlaces: 1
        },
        mb: {
            string: F.output.format_strs.uploadr_filesize_mb,
            scale: 1024 * 1024,
            decimalPlaces: 2
        },
        gb: {
            string: F.output.format_strs.uploadr_filesize_gb,
            scale: 1024 * 1024 * 1024,
            decimalPlaces: 2
        },
        decimal: ".",
        result_format: '<!! dev="chronic">%number %units</!!>'
    };
    var H = (E[J] ? E[J] : E.bytes);
    if (!D) {
        D = "bytes";
        var K = A * H.scale;
        if (K >= E.gb.scale) {
            D = "gb"
        } else {
            if (K >= E.mb.scale) {
                D = "mb"
            } else {
                if (K >= E.kb.scale) {
                    D = "kb"
                }
            }
        }
    }
    var I = (E[D] ? E[D] : null);
    var C = H.scale / I.scale;
    var B = (A * C).toFixed(I.decimalPlaces);
    if (B.substr(B.length - 3) == ".00") {
        B = B.substr(0, B.length - 3)
    }
    if (B.substr(B.length - 2) == ".0") {
        B = B.substr(0, B.length - 2)
    }
    var G = E.result_format.replace("%number", (B.toString().replace(".", E.decimal)));
    G = G.replace("%units", I.string);
    return G
};
function toggleModeration() {
    var A = _ge("moderation_toggle");
    if (YAHOO.util.Dom.hasClass(A, "open")) {
        YAHOO.util.Dom.removeClass(A, "open")
    } else {
        YAHOO.util.Dom.addClass(A, "open")
    }
}
function showBandwidthNotice() {
    var H = _ge("bandwidth");
    if (!H) {
        return false
    }
    var C = _ge("bandwidth-used");
    var A = _bytes_used;
    var G = _bytes_left;
    var E = (A / G);
    var D = F.format_file_size(G, "mb");
    var B = "low";
    if (E > 0.5) {
        B = "moderate"
    } else {
        if (E > 0.75) {
            B = "high"
        }
    }
    C.className = B
}
var flashVersion = null;
F.uploadr = null;
F.Uploadr = function() {
    writeDebug("UPLOADR()");
    var D = function(I) {
        return document.getElementById(I)
    };
    var C = this;
    var B = C;
	this.url = upload_Path;
    this.data = {
        bytesUploaded: 0,
        bytesTotal: 0,
        fileCount: 0,
        videoCount: 0
    };
    this.params = {
        api_key: global_magisterLudi,
        async: true,
        auth_hash: global_auth_hash,
        auth_s: _get_cookie("cookie_session"),
        chronic: 1
    };
    this.stats = {
        photos: 0,
        bytes: 0,
        upload_time: 0,
        errors: 0,
        chronic: 1
    };
    this.upload_disabled = false;
    this.didStatsSubmit = false;
    this.statsTimer = null;
    var E = 0;
    this.async = this.params.async;
    var A = {
        ANIM: 0.6,
        EASE: YAHOO.util.Easing.easeBothStrong,
        FILE_TIMEOUT: 1000 * 60,
        FOO: "BAR"
    };
    this.mb = 1024 * 1024;
    this.oversizeFiles = 0;
    this.excludedFiles = 0;
    this.maxFileSize = (_upload_max_photo_size * this.mb);
    this.uploadActive = false;
    this.uploadStarted = false;
    this.uploadComplete = false;
    this.files = [];
    this.photoIDs = [];
    this.failedFiles = 0;
    this.failedFilesBytes = 0;
    this.currentFile = null;
    this.isEmpty = true;
    this.fileTypesPhotos = [{
        description: F.output.format_strs.uploadr_filetypes_photos,
        extensions: "*.jpg;*.JPG;*.jpeg;*.JPEG;*.bmp;*.BMP;*.gif;*.GIF;*.png;*.PNG;*.tif;*.TIF;*.tiff;*.TIFF"
    }];
    this.fileTypesPhotosAndVideos = [{
        description: F.output.format_strs.uploadr_filetypes_photos_and_videos,
        extensions: this.fileTypesPhotos[0].extensions + ""
    }];
    this.fileTypes = (_video_offline ? this.fileTypesPhotos: this.fileTypesPhotosAndVideos);
    this.maxFileSize = {
        fs_default: _upload_max_photo_size * this.mb,
        fs_video: (typeof _upload_max_video_size != "undefined" ? _upload_max_video_size: 0) * this.mb
    };
    this.getFileType = function(J) {
        J = J.toLowerCase();
        var K = ["mov", "mpg", "mpeg", "mp4", "avi", "wmv", "3g2", "3gp", "m4v"];
        for (var I = K.length; I--;) {
            if (J.indexOf("." + K[I]) != -1) {
                return "video"
            }
        }
        return "image"
    };
    this.fileTypes[0].description += " (" + this.fileTypes[0].extensions + ")";
    this.o = D("upload-table");
    this.oWidth = 0;
    this.barWidth = 800;
    this.barOffset = 60;
    this.oForm = D("upload_form");
    this.oControl = null;
    this.oAdd = D("upload-add-files");
    this.oAddMore = D("upload-add-more");
    this.oRemove = D("upload-remove-files");
    this.oList = D("uploadr-list");
    this.oStatus = D("upload-container");
    this.oUpOverSize = D("upload-oversize");
    this.oUpOverLimit = D("upload-overlimit");
    this.oUpNoVideo = D("upload-no-video");
    this.oUpOneFailed = D("uploadr-one-failed");
    this.oUpSomeFailed = D("uploadr-some-failed");
    this.oUpAllFailed = D("uploadr-all-failed");
    this.oStatusProgress = D("uploadr-status").getElementsByTagName("div")[0];
    this.oOffline = document.createDocumentFragment();
    this.strings = {
        messages: {
            overSize: this.oUpOverSize.innerHTML,
            overLimit: this.oUpOverLimit.innerHTML,
            upOneFailed: this.oUpOneFailed.innerHTML,
            upSomeFailed: this.oUpSomeFailed.innerHTML,
            upAllFailed: this.oUpAllFailed.innerHTML
        },
        fileCountSingle: YAHOO.util.Dom.getElementsByClassName("single", "span", D("upload-total-files"))[0].innerHTML,
        fileCountPlural: YAHOO.util.Dom.getElementsByClassName("plural", "span", D("upload-total-files"))[0].innerHTML,
        bytesCount: D("upload-total-bytes").innerHTML,
        pageTitleStatus: YAHOO.util.Dom.getElementsByClassName("title-status", "li", D("upload-templates"))[0].innerHTML.replace("%pagetitle", document.title),
        pageTitleProcessing: YAHOO.util.Dom.getElementsByClassName("title-processing", "li", D("upload-templates"))[0].innerHTML.replace("%pagetitle", document.title),
        pageTitleDefault: document.title
    };
    this.lastState = null;
    var H = (isIE && navigator.appVersion.indexOf("MSIE 6") != -1);
    var G = (navigator.userAgent.match(/firefox/i));
    if (G) {
        YAHOO.util.Dom.addClass(C.oStatus, "firefox")
    }
    this.getControl = function() {
        if (C.oControl) {
            writeDebug("uploadr.getControl: Already got control");
            return false
        }
        writeDebug("uploadr.getControl");
        C.oControl = uploader
    };
    this.getMaxFileSize = function(I) {
        var J = (I == "video");
        var K = C.maxFileSize[(J ? "fs_video": "fs_default")];
        return K;
    };
    this.getModerationSettings = function() {
		C.params.is_friend = 1;
		C.params.is_family = 1;
    };
    this.checkContentSettings = function() {
        return ({
            is_restricted: D("restricted").checked,
            is_default: (_default_content_type_restricted == 1)
        })
    };
    this.checkRestrictedRules = function() {
        var N = D("video_restricted");
        var L = YAHOO.util.Dom.hasClass(N, "open");
        function J() {
            N.style.position = "absolute";
            N.style.top = "-9999px";
            N.style.height = "auto";
            N.className = K;
            var O = N.offsetHeight - 4;
            N.style.overflow = "hidden";
            N.style.height = "1px";
            N.style.position = "relative";
            N.style.top = "0px";
            M = new YAHOO.util.Anim(N, {
                height: {
                    from: 1,
                    to: O
                },
                opacity: {
                    from: 0,
                    to: 1
                }
            },
            0.33, YAHOO.util.Easing.easeBothStrong);
            M.onComplete.subscribe(function() {
                N.style.height = "auto";
                N.style.overflow = ""
            });
            M.animate()
        }
        function I() {
            N.style.overflow = "hidden";
            M = new YAHOO.util.Anim(N, {
                height: {
                    from: N.offsetHeight - 4,
                    to: 1
                },
                opacity: {
                    to: 0
                }
            },
            0.33, YAHOO.util.Easing.easeBothStrong);
            M.onComplete.subscribe(function() {
                N.className = K;
                N.style.overflow = ""
            });
            M.animate()
        }
        var M = null;
        if (C.data.videoCount > 0 && D("restricted").checked) {
            var K = "open";
            if (C.data.videoCount == C.data.fileCount) {
                K += " video-only"
            } else {
                K += " mixed-content"
            }
            if (_default_content_type_restricted == 1) {
                K += " default"
            } else {
                K += " non-default"
            }
            if (!D("moderation").className.match(/open/i)) {
                toggleModeration();
                upPage.toggle()
            }
            if (!L && K.indexOf("open") != -1) {
                J()
            } else {
                if (L && K.indexOf("open") == -1) {
                    I()
                } else {
                    N.className = K
                }
            }
            return false
        } else {
            I();
            return true
        }
    };
    this.checkNonProVideo = function() {
        if (global_ispro) {
            return true
        } else {
            var I = false;
            var J = null;
            for (J in C.files) {
                if (C.files[J].fileType == "video") {
                    I = true;
                    break
                }
            }
            if (I) {
                C.oUpNoVideo.style.display = "block"
            } else {
                C.oUpNoVideo.style.display = "none"
            }
        }
    };
    this.setState = function(I) {
        if ((I == "canceled" || I == "failed") && C.lastState == "ok") {
            return false
        }
        if (C.lastState) {
            YAHOO.util.Dom.removeClass(C.oStatus, C.lastState)
        }
        C.lastState = I;
        YAHOO.util.Dom.addClass(C.oStatus, I)
    };
    this.setPageTitle = function(I, J, M) {
        try {
            var L = (typeof I != "undefined" ? C.strings[I].replace(J, M) : C.strings.pageTitleDefault);
            if (document.title != L) {
                document.title = L
            }
        } catch(K) {}
    };
    this.setPageIcon = function(I) {
        if (!G) {
            return false
        }
        if (!I) {
            var I = "/favicon.ico"
        }
        if (D("oldPageIcon")) {
            D("oldPageIcon").parentNode.removeChild(D("oldPageIcon"))
        }
        link = document.createElement("link");
        link.id = "oldPageIcon";
        link.href = I;
        link.type = (isIE ? "image/vnd.microsoft.icon": "image/ico");
        link.rel = (isIE ? "shortcut icon": "icon");
        document.getElementsByTagName("head")[0].appendChild(link)
    };
    this.File = function(J) {
        var I = this;
        this.o = null;
        this.oWidth = 0;
        this.oCheckbox = null;
        this.oRemove = null;
        this.complete = false;
        this.state = "default";
        this.lastState = this.state;
        this.nextState = null;
        this.oData = J;
        this.oStub = document.createElement("div");
        this.bestDate = J.bestDate;
        this.bestDateStamp = null;
        this.data = {
            id: J.id,
            url: J.url,
            bytesTotal: J.bytesTotal,
            mDate: J.mDate,
            cDate: J.cDate,
            debugInfo: [],
            bytesUploaded: 0,
            lastBytesUploaded: 0
        };
        this.isExcluded = false;
		this.isOversize = false;
        this.fileType = B.getFileType(this.data.url);
/*      this.maxFileSize = B.getMaxFileSize(this.fileType);
        this.isOversize = (this.data.bytesTotal > this.maxFileSize); */
        if (this.fileType == "video") {
            this.isOversize = false;
            this.isExcluded = false;
            //B.excludedFiles++
        }
        if (this.isOversize) {
            this.isExcluded = true;
            B.oversizeFiles++;
            B.excludedFiles++
        }
        this.poll = {
            retryCount: 0,
            pendingResponse: 0,
            pollCount: 0,
            pollCountSegments: 4,
            pollOffset: 0
        };
        this.timeoutTimer = null;
        this.oAnimPoll = null;
        this.ticket = null;
        this.isOverLimit = false;
        this.allowUpload = (!this.isExcluded);
        this.reset = function() {
            if (I.state == "error") {
                I.setTooltip("");
                I.data.bytesUploaded = 0;
                I.data.lastBytesUploaded = 0;
                I.nextState = null;
                I.setState("default");
                I.animateColor("#000");
                if (I.oAnimPoll) {
                    I.oAnimPoll.stop()
                }
                I.o.style.backgroundPosition = ( - B.barWidth + "px 0px")
            }
        };
        this.timeoutCheck = function() {
            I.timeoutTimer = null;
            if (I.state == "uploading") {
                B.doFileError(I);
                I.setTooltip("file upload stall/timeout");
                B.oControl.cancel(I.data.id);
                B.refreshProgress();
                window.setTimeout(B.uploadNextFile, 1000)
            }
        };
        this.setTimeoutEvent = function() {
            if (I.timeoutTimer) {
                window.clearTimeout(I.timeoutTimer)
            }
            I.timeoutTimer = window.setTimeout(I.timeoutCheck, A.FILE_TIMEOUT)
        };
        this.animatePollingTween = function() {
            if (!I || I.nextState == "ok" || I.nextState == "error") {
                return false
            }
            var K = parseInt(this.getAttribute("marginLeft"));
            if (isNaN(K) || (this.currentFrame < this.totalFrames && K == 0)) {
                return false
            }
            I.poll.pollOffset = K;
            I.o.style.backgroundPosition = (K + "px 0px")
        };
        this.animateColor = function(L) {
            var K = new YAHOO.util.ColorAnim(I.o, {
                color: {
                    to: L
                }
            },
            A.ANIM, YAHOO.util.Easing.easeBoth);
            K.animate()
        };
        this.resetBackground = function() {
            I.oSelect.style.backgroundPosition = "50% 32px"
        };
        this.animateOut = function() {
            var K = new YAHOO.util.BgPosAnim(I.o, {
                backgroundPosition: {
                    from: [ - B.barWidth + I.oWidth, 0],
                    to: [ - B.barWidth + I.oWidth, 24],
                    unit: ["px", "px"]
                }
            },
            A.ANIM, YAHOO.util.Easing.bounceOut);
            var L = new YAHOO.util.BgPosAnim(I.oSelect, {
                backgroundPosition: {
                    from: [50, 24],
                    to: [50, 5],
                    unit: ["%", "px"]
                }
            },
            A.ANIM, YAHOO.util.Easing.bounceOut);
            I.resetBackground();
            I.setState(I.nextState);
            I.resetBackground();
            if (I.nextState == "ok") {
                K.onComplete.subscribe(function() {
                    I.o.style.backgroundImage = "none"
                })
            }
            K.animate();
            L.animate()
        };
        this.animateProcessingOut = function() {
            var L = -B.barWidth + parseInt(I.data.bytesUploaded / I.data.bytesTotal * I.oWidth);
            var K = new YAHOO.util.BgPosAnim(I.o, {
                backgroundPosition: {
                    from: [I.poll.pollOffset, 0],
                    to: [L, 0],
                    unit: ["px", "px"]
                }
            },
            A.ANIM, A.EASE);
            K.onComplete.subscribe(function() {
                I.animateOut()
            });
            K.animate()
        };
        this.animatePoll = function(P) {
            var M = -B.barWidth + I.oWidth;
            var O = M - B.barOffset;
            var N = O;
            var K = M;
            I.poll.pollOffset = N;
            var L = new YAHOO.util.Anim(I.oStub, {
                marginLeft: {
                    from: O,
                    to: M
                }
            },
            P, YAHOO.util.Easing.easeNone);
            L.onTween.subscribe(I.animatePollingTween);
            I.oAnimPoll = L;
            window.setTimeout(function() {
                L.animate()
            },
            A.ANIM * 1000)
        };
        this.startPolling = function() {
            I.animatePoll(B.poll.delay * (Math.pow(2, B.poll.retryCountMax)) / 1000)
        };
        this.onUploadComplete = function() {};
        this.onComplete = function() {
            I.complete = true;
            I.animateColor("#999");
            I.nextState = "ok";
            I.animateProcessingOut();
            B.removeFile(I.data.id);
            B.updateTicketRequests()
        };
        this.onError = function() {
            I.nextState = "error";
            I.animateProcessingOut();
            I.data.bytesUploaded = 0;
            I.poll.pollCount = 0;
            I.animateColor("#cc3333")
        };
        this.setState = function(K) {
            if ((K == "canceled" || K == "failed") && I.lastState == "ok") {
                return false
            }
            if ((I.isExcluded || I.isOverLimit) && K == "ok") {
                return false
            }
            if (I.lastState) {
                YAHOO.util.Dom.removeClass(I.o, I.lastState)
            }
            I.state = K;
            I.lastState = K;
            YAHOO.util.Dom.addClass(I.o, K)
        };
        this.setSelected = function(K) {
            if (K) {
                return I.select()
            } else {
                return I.deselect()
            }
        };
        this.select = function() {
            if (!I.isSelected) {
                YAHOO.util.Dom.addClass(I.o, "selected");
                I.isSelected = true;
                return true
            }
            return false
        };
        this.deselect = function() {
            if (I.isSelected) {
                YAHOO.util.Dom.removeClass(I.o, "selected");
                I.isSelected = false;
                return true
            }
            return false
        };
        this.focus = function() {
            var O = D("uploadr-scroll");
            var L = parseInt(I.o.offsetHeight);
            var N = parseInt(I.o.offsetTop);
            var M = parseInt(O.offsetHeight);
            var Q = parseInt(O.scrollTop);
            var P = ((N + L) > (Q + M));
            var K = (Q > N);
            if ((P || K) && !O._isScrolling) {
                var R = new YAHOO.util.Scroll(O, {
                    scroll: {
                        to: [0, N - (P ? L: 0)]
                    }
                },
                A.ANIM * 2, A.EASE);
                O._isScrolling = true;
                R.onComplete.subscribe(function() {
                    O._isScrolling = false
                });
                R.animate()
            }
            I.oWidth = parseInt(B.oList.offsetWidth)
        };
        this.setTooltip = function(L, K) {
            if (typeof K == "undefined") {
                K = ""
            }
            I.data.debugInfo[I.data.debugInfo.length] = L + K;
            if (_show_tooltips != "1") {
                return false
            }
            I.o.getElementsByTagName("div")[0].innerHTML = (I.data.url.indexOf("/") == -1 ? I.data.url: I.data.url.substr(I.data.url.lastIndexOf("/"))) + (L ? " <b>A/D: " + L + K + "</b>": "");
            I.o.title = L.unescape_from_xml()
        };
        this.init = function() {
            var K = 0;
            I.o = D("tmpl-upload-item").cloneNode(true);
            I.oSelect = I.o.getElementsByTagName("div")[2];
            I.o.id = "";
            I.o.title = I.data.url + " - " + new Date(I.bestDate).toLocaleString();
            I.o.getElementsByTagName("div")[0].innerHTML = (I.data.url.indexOf("/") == -1 ? I.data.url: I.data.url.substr(I.data.url.lastIndexOf("/")));
            I.oRemove = I.o.getElementsByTagName("a")[0];
            YAHOO.util.Event.addListener(I.oRemove, "click", I.removeAndDestruct, I);
            var L = I.o.getElementsByTagName("div")[3];
            L.innerHTML = L.innerHTML.replace("%filesize", F.format_file_size(I.data.bytesTotal));
            if (I.isExcluded) {
                I.setState("oversize");
                K = 1
            }
            B.oOffline.appendChild(I.o);
            I.oWidth = parseInt(B.oList.offsetWidth);
            if (!I.oWidth) {
                I.oWidth = (global_ispro ? 736 : 400)
            }
            I.poll.pollOffset = -B.barWidth + I.oWidth - B.barOffset;
            return K
        };
        this.setData = function(L) {
            var K = null;
            for (K in L) {
                I.data[K] = L[K]
            }
        };
        this.refreshProgress = function(K) {
            if (K) {
                I.data.bytesUploaded = I.data.bytesTotal
            }
            var N = (I.data.bytesUploaded == I.data.bytesTotal);
            if (N && !I.o._isAnimating && I.lastState != "processing" && I.lastState != "complete") {
                I.setState("processing");
                I.resetBackground();
                I.startPolling()
            } else {
                if (!I.complete && I.lastState != "uploading" && I.lastState != "processing") {
                    I.setState("uploading")
                }
            }
            var M = -B.barWidth + parseInt(I.data.bytesUploaded / I.data.bytesTotal * (I.oWidth - B.barOffset));
            if (!I.o._isAnimating && I.data.lastBytesUploaded == 0 && I.data.bytesUploaded == I.data.bytesTotal) {
                var O = -B.barWidth + parseInt(I.data.lastBytesUploaded / I.data.bytesTotal * (I.oWidth - B.barOffset));
                I.o._isAnimating = true;
                var L = new YAHOO.util.BgPosAnim(I.o, {
                    backgroundPosition: {
                        from: [O, 0],
                        to: [M, 0],
                        unit: ["px", "px"]
                    }
                },
                A.ANIM, A.EASE);
                L.onComplete.subscribe(function() {
                    I.o._isAnimating = false;
                    I.setState("processing")
                });
                L.animate()
            } else {
                if (!I.o._isAnimating && I.state != "failed") {
                    I.o.style.backgroundPosition = (M + "px 0px")
                }
            }
            I.data.lastBytesUploaded = I.data.bytesUploaded
        };
        this.remove = function() {
            if (I.isOversize) {
                B.oversizeFiles--
            }
            if (I.isExcluded) {
                B.excludedFiles--
            }
            if (!B.uploadStarted) {
                I.o.parentNode.removeChild(I.o)
            }
        };
        this.removeAndDestruct = function(K) {
            B.removeFile(I.data.id);
            YAHOO.util.Event.preventDefault(K || event);
            return false
        };
        this.destruct = function() {
            I.o = null;
            I = null
        }
    };
    this.fileExists = function(L) {
        var K = false;
        var I = null;
        var J = null;
        for (I in C.files) {
            K = true;
            for (J in C.files[I].oData) {
                if (J != "id" && (C.files[I]).oData[J] != L[J]) {
                    K = false
                }
            }
            if (K) {
                return true
            }
        }
        return false
    };
    this.addFile = function(J) {
        if (C.files[J.id]) {
            return false
        }
        if (C.fileExists(J)) {
            return false
        }
        C.files[J.id] = new C.File(J);
        var I = C.files[J.id].init();
        if (I == 0) {
            C.data.fileCount++;
            if (C.files[J.id].fileType == "video") {
                C.data.videoCount++
            }
            C.data.bytesTotal += parseInt(J.bytesTotal)
        }
        if (C.isEmpty) {
            C.isEmpty = false;
            C.setStep(2)
        }
    };
    this.selectFiles = function() {
        writeDebug("uploadr.selectFiles()");
        if (C.oControl.browseMultiple) {
            try {
                C.oControl.browseMultiple(C.fileTypes)
            } catch(I) {
                goOldSkool({
                    reason: "jsError,selectFiles()"
                })
            }
        } else {
            writeDebug("WARN: self.oControl.browseMultiple null/undefined?");
            goOldSkool({
                reason: "selectFiles(),unsupported"
            })
        }
        writeDebug("uplaodr.selectFiles() complete");
        return false
    };
    this.stepFlashTargets = ["upload-add-files", "upload-add-more"];
    this.currentStep = 0;
    this.setStep = function(I) {
        C.currentStep = I;
        var J = D("uploadr");
        if (!YAHOO.util.Dom.hasClass(J, I)) {
            J.className = "step" + I + (global_ispro ? " pro": "")
        }
        if (I == 2) {
            hideFlash()
        } else {
            overlayFlashOnElement(_ge(C.stepFlashTargets[C.currentStep - 1]))
        }
    };
    this.removeFile = function(I, J) {
        if (!C.files[I] || C.uploadStarted) {
            return false
        }
        if (!C.files[I].isExcluded) {
            C.data.fileCount--;
            C.data.bytesTotal -= parseInt(C.files[I].data.bytesTotal)
        }
        if (C.files[I].fileType == "video") {
            C.data.videoCount--
        }
        C.files[I].remove();
        C.oControl.removeFile(C.files[I].data.id);
        C.files[I].destruct();
        delete C.files[I];
        C.doEmptyCheck();
        if (!J) {
            B.refreshTotals();
            B.refreshMessages()
        }
    };
    this.removeAllFiles = function() {
        for (var I in C.files) {
            C.removeFile(C.files[I].data.id, true)
        }
        B.refreshTotals();
        B.refreshMessages()
    };
    this.removeVideos = function() {
        for (var I in C.files) {
            if (C.files[I].fileType == "video") {
                C.removeFile(C.files[I].data.id, true)
            }
        }
        B.refreshTotals();
        B.refreshMessages()
    };
    this.refreshTotals = function() {
        var I = [C.strings.fileCountSingle.replace("%files", C.data.fileCount), C.strings.fileCountPlural.replace("%files", C.data.fileCount)];
        YAHOO.util.Dom.getElementsByClassName("single", "span", D("upload-total-files"))[0].innerHTML = I[0];
        YAHOO.util.Dom.getElementsByClassName("plural", "span", D("upload-total-files"))[0].innerHTML = I[1];
        D("upload-total-files").className = (C.data.fileCount != 1 ? "plural": "single");
		
		var AdnanStr = F.format_file_size(C.data.bytesTotal);
		AdnanTotal = C.data.bytesTotal / (1024*1024);
		if(AdnanTotal > _upload_Total_max_limit){
			C.disableUpload();
			AdnanStr = '<span style="color:red;">' + AdnanStr + '</span>';
		}
		else C.enableUpload();
		
        D("upload-total-bytes").innerHTML = C.strings.bytesCount.replace("%bytes", AdnanStr);
    };
    this.refreshMessages = function() {
        var P = D("uploadr-messages");
        if (C.oversizeFiles > 0) {
            C.oUpOverSize.innerHTML = C.strings.messages.overSize.replace("%filecount", C.oversizeFiles);
            YAHOO.util.Dom.addClass(P, "oversize")
        } else {
            YAHOO.util.Dom.removeClass(P, "oversize")
        }
        var L = null;
        var J = 0;
        var O = 0;
        var M = _ge("bandwidth-used");
        I = 0; //(global_ispro ? 0 : parseInt(M.innerHTML));
        var I = 0; //(global_ispro ? 0 : parseInt(M.innerHTML));
        var N = 8192 * 1024 * 1024; //(global_ispro ? 8192 * 1024 * 1024 : _bytes_left);
        for (L in C.files) {
            if (!C.files[L].isExcluded) {
                J += C.files[L].data.bytesTotal;
                if (J > N) {
                    O++;
                    C.files[L].isOverLimit = true;
                    YAHOO.util.Dom.addClass(C.files[L].o, "overlimit")
                } else {
                    if (C.files[L].isOverLimit) {
                        C.files[L].isOverLimit = false;
                        C.files[L].deselect();
                        YAHOO.util.Dom.removeClass(C.files[L].o, "overlimit")
                    }
                }
            }
        }
        if (O > 1) {
            for (L in C.files) {
                if (C.files[L].isOverLimit) {
                    C.files[L].select()
                } else {
                    C.files[L].deselect()
                }
            }
            var K = C.strings.messages.overLimit.replace("%filecount", C.data.fileCount);
            K = K.replace("%filesize", F.format_file_size(C.data.bytesTotal));
            K = K.replace("%extrabytes", F.format_file_size(Math.max(0, C.data.bytesTotal - N)));
            C.oUpOverLimit.innerHTML = K;
            YAHOO.util.Dom.addClass(P, "overlimit");
            YAHOO.util.Dom.addClass(C.o, "overlimit");
        } else {
            YAHOO.util.Dom.removeClass(P, "overlimit");
            YAHOO.util.Dom.removeClass(C.o, "overlimit")
        }
        C.canHasUpload();
        overlayFlashOnElement(_ge(C.stepFlashTargets[C.currentStep - 1]));
        C._onMouseOut()
    };
    this.restrictedUpdate = function() {
        C.canHasUpload()
    };
    this.canHasUpload = function() {
        var I = (C.data.fileCount > 0);
        var J = true;

            //J = C.checkRestrictedRules()
			
        if (I && J) {
            writeDebug("enabling upload");
            C.enableUpload()
        } else {
            writeDebug("disabling upload - " + (!I ? "No stuff to upload ": "") + (!J ? "Restricted rules not OK": ""));
            C.disableUpload()
        }
		
		AdnanTotal = C.data.bytesTotal / (1024*1024);
		if(AdnanTotal > _upload_Total_max_limit){
			C.disableUpload();
		}
		else C.enableUpload();
    };
    this.doEmptyCheck = function() {
        if (C.oList.getElementsByTagName("li").length == 1 && !C.isEmpty) {
            C.isEmpty = true;
            C.setButtonState("");
            C.setStep(1)
        } else {
            if (C.isEmpty) {
                C.isEmpty = false;
                YAHOO.util.Dom.addClass(C.o, "has-content")
            }
        }
    };
    this.setData = function(J) {
        var I = null;
        for (I in J) {
            C.data[I] = J[I]
        }
    };
    this.setFileState = function(J) {
        var I = null;
        for (I in C.files) {
            C.files[I].setState(J)
        }
    };
    this.setButtonState = function(I) {
        D("upload-buttons").className = (typeof I != "undefined" ? I: "default")
    };
    this.setCompleteState = function(I) {
        D("uploadr-complete-messages").className = I
    };
    this.doAdJax = function() {

	};
    this.enableUpload = function() {
        if (C.upload_disabled == false) {
            return false
        }
        C.upload_disabled = false;
        YAHOO.util.Dom.removeClass(D("start-upload"), "not-allowed");
        window.setTimeout(function() {
            overlayFlashOnElement(_ge(C.stepFlashTargets[C.currentStep - 1]))
        },
        20)
    };
    this.disableUpload = function() {
        if (C.upload_disabled == true) {
            return false
        }
        C.upload_disabled = true;
        YAHOO.util.Dom.addClass(D("start-upload"), "not-allowed");
        overlayFlashOnElement(_ge(C.stepFlashTargets[C.currentStep - 1]))
    };
    this.startUpload = function(I) {
		// validation for titles
		/*var titles = document.getElementsByName('fileTitle[]');
		var tags = document.getElementsByName('fileTags[]');
		for(i = 1; i<titles.length; i++){
			if(titles[i].value == ''){
				alert('Please enter a Title for you file');
				titles[i].focus();
				return false;
			} /* else if(tags[i].value == ''){
				alert('Please enter tags for you file');
				tags[i].focus();
				return false;
			} */
		//}
        if (C.uploadActive || C.upload_disabled) {
            return false
        }
        E = new Date();
        C.uploadActive = true;
        C.uploadStarted = true;
        F.changes_were_made();
        C.oWidth = parseInt(C.oList.offsetWidth);
        C.getModerationSettings();
        YAHOO.util.Dom.addClass(C.o, "no-remove");
        C.setState("uploading");
        C.setPageIcon(_images_root + "/upload/balls-16x16-nopad-fortab2.gif");
        C.setButtonState("uploading");
        YAHOO.util.Dom.addClass(C.oStatus, "active");
        YAHOO.util.Dom.addClass(C.oStatus, "uploading");
        hideFlash();
        if (global_ispro) {
            C.uploadNextFile()
        } else {
            C.doAdJax();
            setTimeout(C.uploadNextFile, 1500)
        }
        if (typeof I != "undefined" || typeof event != "undefined") {
            YAHOO.util.Event.preventDefault(I || event)
        }
        return false
    };
    this.cancelUpload = function() {
        if (!C.uploadActive) {
            return false
        }
        C.uploadActive = false;
        F.changes_were_saved();
        C.oControl.cancel();
        C.setFileState("canceled");
        C.setButtonState("finished");
        C.setState("canceled");
        C.setPageTitle()
    };
    this.retryUpload = function() {
        C.setCompleteState("");
        C.failedFiles = 0;
        C.failedFilesBytes = 0;
        C.resetBadFiles();
        C.uploadActive = false;
        F.changes_were_saved();
        C.setState("uploading");
        E = new Date();
        C.uploadNextFile()
    };
    this.scrollToTop = function() {
        var I = new YAHOO.util.Scroll(D("uploadr-scroll"), {
            scroll: {
                to: [0, 0]
            }
        },
        A.ANIM * 2, A.EASE);
        I.animate()
    };
    this.resetBadFiles = function() {
        var I = null;
        for (I in C.files) {
            C.files[I].reset()
        }
    };
    this.uberSort = function(J, I) {
        var L = J.toLowerCase();
        var K = I.toLowerCase();
        return (L > K) ? 1 : ((L < K) ? -1 : 0)
    };
    this.uploadNextFile = function() {
        var I = null;
        var J = false;
        for (I in C.files) {
            if (!J && C.files[I].allowUpload && C.files[I].state == "default") {
                J = true;
                if (C.currentFile && C.async) {
                    C.data.bytesUploaded += C.currentFile.data.bytesTotal
                }
                C.currentFile = C.files[I];
                C.files[I].focus();
                C.files[I].setState("uploading");
                C.files[I].setTimeoutEvent();
                C.uploadMostDefinitely(C.files[I].data)
            }
        }
        if (!J) {
            if (C.async && C.poll.tickets.length) {
                C.setPageTitle("pageTitleProcessing");
                window.setTimeout(C.uploadNextFile, 500)
            } else {
                if (C.async) {
                    C.onCompleteAnimation(C._onComplete)
                } else {
                    C._onComplete()
                }
            }
        }
    };
    this.mergeObjects = function(J, I) {
        var M = {};
        for (var K in J) {
            M[K] = J[K]
        }
        var L = (typeof I == "undefined" ? _s.defaultOptions: I);
        for (var N in L) {
            if (typeof M[N] == "undefined") {
                M[N] = L[N]
            }
        }
        return M
    };
    this.uploadMostDefinitely = function(J) {
        var O = [];
        for (var M in B.params) {
            O[O.length] = M + B.params[M]
        }
        O[O.length] = "Filename" + J.url;
        O[O.length] = "UploadSubmit Query";
        O.sort();
        var L = O.join("");
		var FileTitle = document.getElementsByName('fileTitle[]')[AdnanCounter].value;
		var Tags = document.getElementsByName('fileTags[]')[AdnanCounter++].value;
		if(AdnanCounter >= document.getElementsByName('fileTitle[]').length) AdnanCounter = 1;
        C.api_sig = md5_calcMD5(escape_utf8_bytes(global_flickr_secret + L));
        var I = C.mergeObjects(C.params, {
            api_sig: C.api_sig
        });
        var N = "";
        var K = null;
        for (K in I) {
            N += K + ": " + I[K] + "\n"
        }
        writeDebug("uploading file with params: " + N);
        C.oControl.upload(J.id, C.url, "POST", {ftitle:FileTitle, ftags:Tags}, "photo")
    };
    this.refreshProgress = function() {
        var J = C.data.bytesTotal;
        var K = C.data.bytesUploaded + C.currentFile.data.bytesUploaded + C.failedFilesBytes;
        C.oStatusProgress.style.width = ((parseInt(K / J * (C.oWidth - C.barOffset))) + "px");
        var I = Math.floor(K / J * 99);
        I = Math.max(0, Math.min(100, I));
        C.setPageTitle("pageTitleStatus", "%percentuploaded", I)
    };
    this.onCompleteAnimation = function(I) {
        var J = new YAHOO.util.Anim(C.oStatusProgress, {
            width: {
                to: C.oWidth + 8
            }
        },
        A.ANIM, A.EASE);
        J.onComplete.subscribe(function() {
            I()
        });
        J.animate()
    };
    this.getFileByTicket = function(I) {
        var J = null;
        for (J in B.files) {
            if (B.files[J].ticket == I) {
                return B.files[J]
            }
        }
    };
    this.getFilesByTickets = function(M) {
        var L = [];
        var K = null;
        for (var J = 0,
        I = M.length; J < I; J++) {
            K = C.getFileByTicket(M[J]);
            if (K) {
                L[L.length] = K
            }
        }
        return L
    };
    this.poll = {
        active: 0,
        tickets: [],
        ticketCount: 0,
        delay: 500,
        pollTimer: null,
        retryCount: 0,
        retryCountMax: 8
    };
    this.startPolling = function() {
        if (C.poll.active) {
            return false
        }
        C.poll.active = true;
        C.pollRequest()
    };
    this.stopPolling = function() {
        if (!C.poll.active) {
            return false
        }
        C.poll.active = false
    };
    this.pollRequest = function() {
        if (C.poll.pendingResponse) {} else {
            C.poll.pendingResponse = true;
            var J = C.getFilesByTickets(C.poll.tickets);
            for (var I = J.length; I--;) {
                if (J[I].poll.pollCount == 0) {
                    J[I].poll.pollCount++
                }
            }
            F.API.callMethod("flickr.photos.upload.checkTickets", {
                tickets: C.poll.tickets.join(",")
            },
            C)
        }
    };
    this.flickr_photos_upload_checkTickets_onLoad = function(Q, P, O, J) {
        C.poll.pendingResponse = false;
        if (P) {
            var N = P.documentElement;
            var I = N.getElementsByTagName("ticket");
            for (var M = 0,
            K = I.length; M < K; M++) {
                C.checkTicket(I[M])
            }
        }
        if (C.poll.tickets.length > 0) {
            C.poll.retryCount++;
            var L = C.poll.delay * (Math.pow(2, C.poll.retryCount));
            C.poll.pollTimer = window.setTimeout(C.pollRequest, L)
        } else {
            C.stopPolling()
        }
    };
    this.checkTicket = function(K) {
        var J = K.getAttribute("complete");
        var M = K.getAttribute("id");
        var L = B.getFileByTicket(M);
        if (!L) {
            C.removeTicket(M);
            return false
        }
        if (J == 0 && L.poll.retryCount < C.poll.retryCountMax) {
            L.poll.retryCount++
        } else {
            if (J == 1) {
                C.removeTicket(M);
                var I = K.getAttribute("photoid");
                C.doFileComplete(L, I)
            } else {
                if (J == 2) {
                    C.removeTicket(M);
                    L.setTooltip("processing conversion failed");
                    C.doFileError(L)
                } else {
                    if (L.poll.retryCount >= L.poll.retryCountMax) {
                        C.removeTicket(M);
                        L.setTooltip("polling (processing) retry timeout");
                        C.doFileError(L)
                    }
                }
            }
        }
        return J
    };
    this.ticketExists = function(I) {
        for (var J = C.poll.tickets.length; J--;) {
            if (C.poll.tickets[J] == I) {
                return true
            }
        }
        return false
    };
    this.addTicket = function(I) {
        if (!C.ticketExists(I)) {
            C.poll.tickets[C.poll.tickets.length] = I;
            C.poll.ticketCount++
        }
    };
    this.removeTicket = function(I) {
        for (var J = 0; J < C.poll.tickets.length; J++) {
            if (C.poll.tickets[J] == I) {
                C.poll.tickets.splice(J, 1);
                C.poll.ticketCount--;
                return true
            }
        }
        return false
    };
    this.updateTicketRequests = function() {
        if (C.async && C.poll.tickets.length && C.poll.pollTimer != null) {
            var I = 500;
            window.clearTimeout(C.poll.pollTimer);
            C.poll.pollTimer = null;
            C.poll.pendingResponse = false;
            window.setTimeout(function() {
                C.poll.pollTimer = window.setTimeout(C.pollRequest, I)
            },
            250)
        }
    };
    this.describePhotos = function() {
        if (!C.photoIDs.length) {
            window.location.reload()
        }
        D("uploadr-ids").value = C.photoIDs.join(",");
        D("photo-edit-form").submit()
    };
    this.sortFilesByName = function(J, I) {
        var L = J.name.toLowerCase();
        var K = I.name.toLowerCase();
        return (L > K) ? 1 : ((L < K) ? -1 : 0)
    };
    this.invalidDate = function(I) {
        return (new Date(I).getYear() < 70)
    };
    this.sortFilesByBestDateStamp = function(J, I) {
        var L = J.bestDateStamp;
        var K = I.bestDateStamp;
        return (L > K) ? 1 : ((L < K) ? -1 : 0)
    };
    this.debugReport = function() {
        var M = "-- Web Uploadr: Debug info --\nuser " + global_nsid + ", " + (new Date().toUTCString() + "\n");
        M += "-----------------------------\n";
        var L = [];
        var K = null;
        var J = false;
        var I = null;
        for (I in C.files) {
            K = C.files[I];
            if (K.data && K.data.debugInfo.length > 0) {
                L[L.length] = (K.data.url + " | " + K.data.bytesTotal + " | " + (K.ticket ? K.ticket: "[none]") + " | " + K.data.debugInfo.join(", "));
                J = true
            }
        }
        if (J) {
            M += "File name | size (bytes) | ticket | reason(s)\n";
            M = M + L.join("\n")
        } else {
            M += "No errors to report for this batch.\n";
            M += "-----------------------------\n"
        }
        alert(M)
    };
    this._onSelect = function(J) {
        writeDebug("uploadr._onSelect()");
        var I = J.fileList;
        var M = [];
        for (L in I) {
            I[L].bestDate = (C.invalidDate(I[L].cDate) ? I[L].mDate: I[L].cDate);
            I[L].bestDateStamp = new Date(I[L].bestDate).getTime();
            M[M.length] = I[L]
        }
        M.sort(C.sortFilesByBestDateStamp);
        var L = null;
        for (var K = 0; K < M.length; K++) {
            writeDebug("adding file: " + M[K].id + " / " + M[K].name);
            C.addFile({
                id: M[K].id,
                url: M[K].name,
                bytesTotal: M[K].size,
                cDate: M[K].cDate,
                mDate: M[K].mDate,
                bestDate: M[K].bestDate
            })
        }
        writeDebug("adding files complete");
        C.oList.appendChild(C.oOffline);
        C.oOffline = document.createDocumentFragment();
        C.refreshTotals();
        C.refreshMessages();
        C.setButtonState();
        writeDebug("uploadr._onSelect(): complete")
    };
    this._onProgress = function(I) {
        var J = I.id;
        if (C.files[J]) {
            C.files[J].data.lastBytesUploaded = C.files[J].data.bytesUploaded;
            C.files[J].setData({
                bytesUploaded: I.bytesLoaded,
                bytesTotal: I.bytesTotal
            });
            C.files[J].refreshProgress();
            C.files[J].setTimeoutEvent();
            C.refreshProgress()
        }
    };
    this._onComplete = function() {
        C.stats.upload_time += (new Date() - E);
        C.setPageIcon();
        C.setButtonState("finished");
        C.setState("complete");
        F.changes_were_saved();
        C.oStatusProgress.style.width = "0px";
        if (C.failedFiles > 0){
            if (C.failedFiles == C.data.fileCount){
                C.setCompleteState("all-failed");
                C.submitStats()
            } else {
                if (C.failedFiles > 1) {
                    C.oUpSomeFailed.innerHTML = C.strings.messages.upSomeFailed.replace("%filecount", C.failedFiles);
                    C.setCompleteState("some-failed")
                } else {
                    C.setCompleteState("one-failed")
                }
            }
            C.uploadStarted = false
        } else {
            C.setCompleteState("ok");
            C.submitStats()
        }
        C.setPageTitle("pageTitleStatus", "%percentuploaded", 100);
        C.uploadComplete = true;
    };
    this._onFileComplete = function(I) {};
    this._onFileCompleteData = function(I) {
        window.setTimeout(function() {
            C._onFileCompleteDataDelayed(I)
        },
        20)
    };
    this._onFileCompleteDataDelayed = function(R) {
        var O = R.id;
        var J = C.files[O];
        if (!J) {
            return false
        }
        writeDebug("API response from Flash: \n" + R.data);
        J.refreshProgress(true);
        var K = F.str_to_XML(R.data);
        var P = (K.getElementsByTagName("rsp")[0] && K.getElementsByTagName("rsp")[0].getAttribute("stat") == "ok");
        var L = K.getElementsByTagName("photoid")[0];
        if (C.async) {
            var M = null;
            try {
                M = (K.getElementsByTagName("rsp")[0].getAttribute("ticketid"));
            } catch(N) {
                M = null
            }
            if (!M) {
                var Q = null;
                try {
                    var I = R.data.escape_for_xml();
                    Q = '<span class="uploadr-debug">' + (I.substr(I.indexOf("&gt;") + 4)) + "</span>"
                } catch(N) {}
                J.setTooltip("bad API response/no ticket", (Q ? Q: ""));
                C.doFileError(J);
                setTimeout(C.uploadNextFile, 50);
                return false
            }
			/*
            J.ticket = M;
            B.addTicket(M);
            B.startPolling();
			*/
			C.doFileComplete(J, L);
            setTimeout(C.uploadNextFile, 50)
        } else {
            if (P && L) {
                L = parseInt(parseInt(K.getElementsByTagName("photoid")[0].childNodes[0].nodeValue));
                C.doFileComplete(J, L)
            } else {
                J.setTooltip("response indicates fail (" + parseInt(parseInt(K.getElementsByTagName("photoid")[0].childNodes[0].nodeValue)) + ")");
                C.doFileError(J)
            }
            setTimeout(C.uploadNextFile, 50)
        }
    };
    this.doFileComplete = function(I, J) {
        if (J) {
            C.photoIDs[C.photoIDs.length] = J
        }
        if (!C.async) {
            C.data.bytesUploaded += I.data.bytesTotal
        }
        I.oSelect.style.backgroundPosition = "50% 32px";
        C.poll.retryCount = Math.max(0, C.poll.retryCount - I.poll.retryCount);
        I.onComplete()
    };
    this.doFileError = function(I) {
        C.stats.errors++;
        C.poll.retryCount = Math.max(0, C.poll.retryCount - I.poll.retryCount);
        I.onError();
        C.failedFiles++;
        C.failedFilesBytes += I.data.bytesTotal;
        if (C.async) {
            C.data.bytesUploaded -= I.data.bytesTotal
        }
    };
    this._onHTTPError = function() {
        C.files[C.currentFile].setTooltip("fatal HTTP error (called from Flash)");
        C.doFileError(C.files[C.currentFile]);
        setTimeout(C.uploadNextFile, 100)
    };
    this._onError = function(I) {
        C.files[C.currentFile].setTooltip("http/io/security error from Flash - " + I.type + ", " + I.status);
        C.doFileError(C.files[C.currentFile]);
        setTimeout(C.uploadNextFile, 100)
    };
    this._onMouseOver = function(I) {
        Y.D.addClass(C.oAdd, "hover");
        Y.D.addClass(C.oAddMore, "hover")
    };
    this._onMouseOut = function(I) {
        Y.D.removeClass(C.oAdd, "hover");
        Y.D.removeClass(C.oAddMore, "hover")
    };
    this.setBGCache = function(I) {
        try {
            document.execCommand("BackgroundImageCache", false, I)
        } catch(J) {}
    };
    this.getStats = function() {
        C.stats.photos = C.data.fileCount;
        C.stats.bytes = C.data.bytesTotal;
        C.stats.upload_time = C.stats.upload_time
    };
    this.submitStats = function() {
        if (C.didStatsSubmit) {
            return false
        }
        C.didStatsSubmit = true;
        C.getStats();
        if (_use_stats == "1") {
            F.API.callMethod("flickr.utils.logUploadStats", C.stats, C)
        }
    };
    this.submitAndContinue = function() {
        if (_use_stats != "1") {
            F.uploadr.describePhotos()
        }
        C.flickr_utils_logUploadStats_onLoad = C.statsSubmitComplete;
        if (!C.didStatsSubmit) {
            if (!C.statsTimer) {
                C.statsTimer = window.setTimeout(F.uploadr.describePhotos, 5000)
            }
            C.submitStats()
        } else {
            F.uploadr.describePhotos()
        }
    };
    this.flickr_utils_logUploadStats_onLoad = function(L, K, I, J) {};
    this.statsSubmitComplete = function(L, K, I, J) {
        if (C.statsTimer) {
            window.clearTimeout(C.statsTimer);
            C.statsTimer = null
        }
        F.uploadr.describePhotos()
    };
    this.destruct = function() {
        B = null;
        C = null
    };
    YAHOO.util.Event.addListener(C.oAdd, "click", C.selectFiles, C);
    YAHOO.util.Event.addListener(D("start-upload"), "click", C.startUpload, C);
    this.initMovie = function() {
        writeDebug("uploadr.initMovie()");
        createUploadMovie()
    };
    this.assignHandlers = function() {
        writeDebug("uploadr.assignHandlers()");
        if (!C.oControl) {
            C.getControl()
        }
        try {
            C.oControl.setAllowMultipleFiles(true);
            uploader.setFileFilters(C.fileTypes);
            C.oControl.addListener("fileSelect", F.uploadr._onSelect);
            C.oControl.addListener("uploadProgress", F.uploadr._onProgress);
            C.oControl.addListener("uploadComplete", F.uploadr._onFileComplete);
            C.oControl.addListener("uploadCompleteData", F.uploadr._onFileCompleteData);
            C.oControl.addListener("uploadError", F.uploadr._onError);
            C.oControl.addListener("rollOver", F.uploadr._onMouseOver);
            C.oControl.addListener("rollOut", F.uploadr._onMouseOut);
            YAHOO.util.Event.addListener("safe", "click", C.restrictedUpdate, C);
            YAHOO.util.Event.addListener("moderate", "click", C.restrictedUpdate, C);
            YAHOO.util.Event.addListener("restricted", "click", C.restrictedUpdate, C)
        } catch(I) {
            writeDebug("uploadr.assignHandlers(): JS/Flash error: " + I.message);
            goOldSkool({
                reason: "jsError,assignHandlers()"
            })
        }
        enableChoosePhotos();
        if (H) {
            C.setBGCache(true);
            YAHOO.util.Event.addListener(window, "beforeunload",
            function() {
                C.setBGCache(false)
            })
        }
        writeDebug("uploadr.assignHandlers(): complete");
        window.setTimeout(function() {
            overlayFlashOnElement(_ge(F.uploadr.stepFlashTargets[0]))
        },
        20)
    };
    return this
};
function uploader_swf_ready() {
    writeDebug("uploader_swf_ready()");
    if (!F.uploadr.oControl) {
        writeDebug("Getting movie..");
        F.uploadr.getControl()
    }
    try {
        writeDebug("assigning handlers..");
        F.uploadr.assignHandlers()
    } catch(A) {
        writeDebug("ERROR: F.uploadr.assignHandlers() failed: " + A.message)
    }
    if (!F.uploadr) {
        writeDebug("WARN: F.uploadr is null?")
    }
    writeDebug("uploadr_swf_ready(): complete")
}
function initUploadr() {
    if (!document.getElementById("uploadr")) {
        writeDebug("#uploadr not found - exiting");
        return false
    }
    flashVersion = deconcept.SWFObjectUtil.getPlayerVersion();
    createYUIUploader();
    F.uploadr = new F.Uploadr();
    writeDebug("F.uploadr: " + F.uploadr);
    F.uploadr.initMovie()
}
function disableChoosePhotos() {
    var A = _ge("step1");
    if (A) {
        A.className = "disabled"
    }
}
function enableChoosePhotos() {
    var A = _ge("step1");
    if (A) {
        A.className = "enabled"
    }
}
function createUploadMovie() {
    writeDebug("createUploadMovie()");
    initYUIUpload()
}
function checkFlash() {
    var A = deconcept.SWFObjectUtil.getPlayerVersion();
    alert(A.major + "." + A.minor + "r" + A.rev)
}
function uploadrSupported() {
    if (window.location.href.match(/nocheck/i)) {
        return true
    }
    var A = deconcept.SWFObjectUtil.getPlayerVersion();
    if (navigator.platform.match(/linux/i) && A.major == 9 && A.minor == 0) {
        if (A.rev < 60) {
            return {
                result: false,
                reason: "needFlashUpgrade"
            }
        } else {
            return {
                result: true
            }
        }
    }
    if (document.getElementById && A.major > 0) {
        if (A.major < 9) {
            return {
                result: false,
                reason: "needFlashUpgrade"
            }
        } else {
            if (A.major == 9 && A.minor == 0 && A.rev == 28) {
                return {
                    result: false,
                    reason: "needFlashUpgrade"
                }
            }
            if (A.major == 9 && A.minor == 0 && A.rev == 16) {
                return {
                    result: false,
                    reason: "badversion,needFlashUpgrade"
                }
            }
            if (A.major == 9 && A.minor == 0 && A.rev == 0) {
                return {
                    result: true
                }
            }
            return {
                result: true
            }
        }
    } else {
        return {
            result: false,
            reason: "noflash"
        }
    }
}
function overlayFlashOnElement(A) {
    if (!A) {
        writeDebug("overlayFlashOnElement(): oTarget null?");
        return false
    }
    writeDebug("overlayFlashOnElement()");
    var E = _ge("upload-flash-box");
    var B = _ge("uploadr");
    var C = Y.D.getXY(B);
    var D = Y.D.getXY(A);
    writeDebug("left/top/w/h: " + D[0] + "px, " + D[1] + "px, " + parseInt(A.offsetWidth) + "px, " + parseInt(A.offsetHeight) + "px");
    E.style.width = "1px";
    E.style.height = "1px";
    E.style.left = (D[0] - C[0] - 2) + "px";
    E.style.top = (D[1] - C[1] - 2) + "px";
    E.style.width = (parseInt(A.offsetWidth) + 4) + "px";
    E.style.height = (parseInt(A.offsetHeight) + 4) + "px"
}
function hideFlash() {
    writeDebug("hideFlash()");
    var A = _ge("upload-flash-box");
    A.style.top = "-999px";
    A.style.left = "-999px"
}
function tryUploadInit() {
    if (!_ge("uploadr")) {
        writeDebug("#uploadr not found - exiting");
        return false
    }
    var A = uploadrSupported();
    if (A.result == false) {
        enableChoosePhotos();
        goOldSkool(A)
    } else {
        initUploadr()
    }
}
function makeKHTMLUpdateUI() {}
if (navigator.userAgent.match(/khtml/i)) {
    window.setInterval(makeKHTMLUpdateUI, 250)
}
YAHOO.util.Event.onDOMReady(function() {
    window.setTimeout(tryUploadInit, 20)
});
var uploader = null;
function initYUIUpload() {
    writeDebug("initYUIUpload()");
    YAHOO.widget.Uploader.SWFURL = "/flu1/yuploadcomponent.swf";
    try {
        uploader = new YAHOO.widget.Uploader("upload-flash-box")
    } catch(B) {
        writeDebug("initYUIUpload(): something broke :" + B.message + " / " + B.toString());
        goOldSkool({
            reason: "jsError,initYUIUpload"
        })
    }
    writeDebug("uploader widget created");
    uploader.addListener("contentReady", uploader_swf_ready);
    function A() {
        if (typeof _upload_logging == "undefined") {
            uploader.setAllowLogging(false)
        } else {
            if (_upload_logging) {
                uploader.setAllowLogging(true)
            }
        }
        uploader.setAllowMultipleFiles(true)
    }
};