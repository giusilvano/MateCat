/*
 * jQuery File Upload Plugin Localization Example 6.5.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2012, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*global window */

window.locale = {
    "fileupload": {
        "errors": {
            "maxFileSize": "MateCat beta supports up to 30 MB.",
            "minFileSize": "File is empty", //minFileSize in options is set to undefined, so this error trigger for 0 byte files
            "acceptFileTypes": "File format not supported",
            "maxNumberOfFiles": "Max number of files exceeded",
            "uploadedBytes": "Uploaded bytes exceed file size",
            "emptyResult": "Empty file upload result"
        },
        "error": "Error:",
        "start": "Start",
        "cancel": "Cancel",
        "destroy": "Delete"
    }
};
