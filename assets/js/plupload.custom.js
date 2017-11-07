
var PluploadCustom = function () {
    var inputName;
    var inputId;
    var plupload;
    return {
        tplUploadItem: function (file, multi = false) {
            var path = file.path;
            if (path === undefined) {
                path = '';
            }
            return this.buildItem(file.id, file.name, path, plupload.formatSize(file.size), multi);
        },
        buildItem: function (id, name, path, size, multi) {
            var temp = '<li class="plupload_file plupload_file_loading" id="' + id + '">';

            temp += this.buildInput(id, path, multi);

            temp += '<div class="plupload_file_thumb"><img src="' + path + '"></div>';

            temp += '<div class="plupload_file_status">' +
                    '<div class="plupload_file_progress">' +
                    '<div class="plupload_file_percent">?</div>' +
                    '<div class="progress">' +
                    '<span class="progress-bar"></span>' +
                    '</div>' +
                    '<span class="plupload_file_mark">准备上传</span>' +
                    '</div>' +
                    '</div>';

            temp += '<div class="plupload_file_name"><span>' + name + '</span></div>';
            temp += '<div class="plupload_file_size">' + size + '</div>';
            temp += '<div class="plupload_file_action">' +
                    '<span class="plupload_action_icon">移除</span>' +
                    '</div>';
            return temp += '</li>';
        },
        buildInput: function (index, value, multi) {
            if (multi) {
                return '<input id="' + this.getInputId(index) + '" name="' + this.getInputName(index) + '" value="' + value + '" type="hidden" class="plupload_file_input">';
            }
            return '';
        },
        getInputId: function (index) {
            return inputId + '-' + index;
        },
        getInputName: function (index) {
            return inputName + '[' + index + ']';
        },
        //main function
        init: function (options = []) {
            if (!window.plupload) {
                return;
            }
            plupload = window.plupload;
            if (options.length > 0) {
                inputName = options.name;
                inputId = options.id;
            }
        }
    };
}();