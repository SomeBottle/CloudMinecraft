var $ = new Object();
var u = './../';
var ep = document.getElementById('statu');
var ec = document.getElementById('cmst');
var timer;
var launched = false;
$.aj = function(p, d, sf, m) {
    /*(path,data,success or fail,method)*/
    var xhr = new XMLHttpRequest();
    var hm = '';
    for (var ap in d) {
        hm = hm + ap + '=' + d[ap] + '&';
    }
    hm = hm.substring(0, hm.length - 1);
    xhr.open('post', p, true);
    if (m !== 'multipart/form-data') {
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send(hm);
    } else {
        xhr.send(d);
    }
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            sf.success(xhr.responseText);
        } else if (xhr.readyState == 4 && xhr.status !== 200) {
            sf.failed(xhr.status);
        }
    };
}
function launch() {
    var keyi = document.getElementById('key');
    var keyt = keyi.value;
    if (!launched) {
        var data = {
            key: keyt
        };
        $.aj(u + 'main.php?action=create', data, {
            success: function(msg) {
                var p = JSON.parse(msg);
                if (p.statu == 'success') {
                    ep.innerHTML = 'Server Creating.';
                    document.getElementById('lc').value = 'Creating.';
                } else {
                    ep.innerHTML = p.msg;
                }
                timer = setInterval(askprogress, 3000);
                launched = true;
            },
            failed: function(msg) {
                ep.innerHTML = '通信失败';
            }
        },
        'post');
    }
}
function askprogress() {
    var keyi = document.getElementById('key');
    var keyt = keyi.value;
    var data = {
        key: keyt
    };
    $.aj(u + 'main.php?action=progress', data, {
        success: function(msg) {
            var p = JSON.parse(msg);
            if (p.statu == 'success') {
                ep.innerHTML = p.msg;
                if (p.msg.indexOf('Successfully Deployed') !== -1) {
                    document.getElementById('lc').value = 'Running.';
                    document.getElementById('st').style.display = 'block';
                }
                if (p.msg.indexOf('terminated') !== -1) {
                    document.getElementById('st').style.display = 'none';
                    document.getElementById('lc').value = 'Launch.';
                    ep.innerHTML = 'Server is terminating/terminated.';
                    clearInterval(timer);
                    launched = false;
                }
            } else {
                document.getElementById('lc').value = 'Launch.';
                document.getElementById('st').style.display = 'none';
                ep.innerHTML = p.msg;
                clearInterval(timer);
                launched = false;
            }
        },
        failed: function(msg) {
            document.getElementById('lc').value = 'Launch.';
            document.getElementById('st').style.display = 'none';
            ep.innerHTML = '通信失败';
            clearInterval(timer);
            launched = false;
        }
    },
    'post');
}
function sendcm() {
    var keyi = document.getElementById('key');
    var keyt = keyi.value;
    var cm = document.getElementById('commandin').value;
    var data = {
        key: keyt,
        command: cm
    };
    $.aj(u + 'main.php?action=sendcommand', data, {
        success: function(msg) {
            var p = JSON.parse(msg);
            if (p.statu == 'success') {
                ec.innerHTML = p.msg;
                document.getElementById('commandin').value = '';
            } else {
                ec.innerHTML = 'Failed:' + p.msg;
            }
        },
        failed: function(msg) {
            ec.innerHTML = 'Command Send Failed';
        }
    },
    'post');
}
function skip() {
    var keyi = document.getElementById('key');
    var keyt = keyi.value;
    var data = {
        key: keyt
    };
    $.aj(u + 'main.php?action=skip', data, {
        success: function(msg) {
            var p = JSON.parse(msg);
            if (p.statu == 'success') {
                document.getElementById('st').style.display = 'none';
                document.getElementById('lc').value = 'Launch.';
            } else {
                ec.innerHTML = 'Failed:' + p.msg;
            }
        },
        failed: function(msg) {
            ec.innerHTML = 'Stop Command Send Failed';
        }
    },
    'post');
}