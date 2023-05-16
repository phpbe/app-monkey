<be-html>
<?php if (false) { ?>
<script>
<?php
}

echo '// ==UserScript==' . "\n";
echo '// @name ' . $this->pullDriver->name . "\n";
// @namespace    liu12
echo '// @version ' . $this->pullDriver->version . "\n";
echo '// @description ' . strip_tags($this->pullDriver->description) . "\n";
// @author       刘一二

echo '// @match ' . $this->pullDriver->match_1 . "\n";
if ($this->pullDriver->match_2 !== '') {
    echo '// @match ' . $this->pullDriver->match_2 . "\n";
}
if ($this->pullDriver->match_3 !== '') {
    echo '// @match ' . $this->pullDriver->match_3 . "\n";
}

echo '// @grant GM_xmlhttpRequest' . "\n";
echo '// @require http://code.jquery.com/jquery-2.1.1.min.js' . "\n";
echo '// @connect ' . \Be\Be::getRequest()->getDomain() . "\n";
echo '// ==/UserScript==' . "\n";
?>
BeMonkey = {

    running: 0,

    step: 'init',

    //当前页面
    currentPage: '',
    // 下一页面
    nextPage: false,

    // 链接
    links: false,

    // 当前分页链接数
    totalPageLinks: 0,

    // 总分页数
    totalPages: 0,

    // 总链接数
    totalLinks: 0,

    park: "rt",

    parkMap: {
        lt: ["left", "top"],
        rt: ["right", "top"],
        lb: ["left", "bottom"],
        rb: ["right", "bottom"],
    },

    // 开始时间
    startTime: '',

    // 结束时间
    endTime: '',

    getNewNextPage: function () {
        <?php echo $this->pullDriver->get_next_page_script; ?>
    },

    getLinks: function () {
        <?php echo $this->pullDriver->get_links_script; ?>
    },

    <?php
    foreach ($this->pullDriver->fields as $index => $field) {
        ?>
        getField_<?php echo $index; ?>: function () {
            <?php echo $field['script']; ?>
        },
        <?php
    }
    ?>

    // 初始化
    init: function () {

        let running = localStorage.getItem("be:monkey:running");
        if (running) {
            this.running = Number(running);
        } else {
            this.running = 0
            localStorage.setItem('be:monkey:running', 0);
        }

        let step = localStorage.getItem("be:monkey:step");
        if (step) {
            this.step = step;
        } else {
            this.step = "init";
            localStorage.setItem("be:monkey:step", "init");
        }

        if (this.step !== "init") {
            let totalPageLinks = localStorage.getItem("be:monkey:totalPageLinks");
            if (totalPageLinks) {
                this.totalPageLinks = totalPageLinks;
            }

            let totalPages = localStorage.getItem("be:monkey:totalPages");
            if (totalPages) {
                this.totalPages = totalPages;
            }

            let totalLinks = localStorage.getItem("be:monkey:totalLinks");
            if (totalLinks) {
                this.totalLinks = totalLinks;
            }
        }

        let currentPage = localStorage.getItem("be:monkey:currentPage");
        if (currentPage) {
            this.currentPage = currentPage;
        } else {
            this.currentPage = "<?php echo $this->pullDriver->start_page; ?>";
        }

        let nextPage = localStorage.getItem("be:monkey:nextPage");
        if (nextPage) {
            this.nextPage = nextPage;
        }

        let links = localStorage.getItem('be:monkey:links');
        if (!links) {
            this.links = [];
        } else {
            this.links = links.split("|");
        }

        let park = localStorage.getItem("be:monkey:park");
        if (park) {
            this.park = park;
        }

        let startTime = localStorage.getItem("be:monkey:startTime");
        if (startTime) {
            this.startTime = startTime;
        }

        let endTime = localStorage.getItem("be:monkey:endTime");
        if (endTime) {
            this.endTime = endTime;
        }

        this.dashboard();

        // console.log(this);

        // 按当前步骤执行操作
        switch (this.step) {
            case "init":
                this.status("待启动...");
                break;
            case "page":
                if (this.running === 1) {
                    this.processPage();
                }
                break;
            case "link":
                if (this.running === 1) {
                    this.processLink();
                }
                break;
            case "complete":
                this.status("采集完成！");
                break;
        }
    },

    // 向页面添加控制台界面
    dashboard: function () {
        var sHtml = '<div id="be-monkey-<?php echo $this->pullDriver->id; ?>" style="position: fixed; padding: 15px; background-color: #fff; width: 400px; font-size:14px; z-index: 99999999;  border: #999 1px solid; opacity: 0.95; box-shadow: 0 0 10px #666; transition: all 0.3s;';
        sHtml += this.parkMap[this.park][0] + ': 10px; '+ this.parkMap[this.park][1] +': 10px;';
        sHtml += '">'

        sHtml += '<div style="font-size: 20px; font-weight: bold;"><?php echo $this->pullDriver->name; ?></div>';

        sHtml += '<div style="padding-top: 10px;">';
        if (this.running === 0) {
            sHtml += '<input type="button" value="' + (this.step === 'init' ? '开如采集' : '重新采集') + '" onclick="BeMonkey.start();">';
        } else if (this.running === 1) {
            sHtml += '<input type="button" value="暂停采集" onclick="BeMonkey.pause();"> <input type="button" value="终止采集" onclick="BeMonkey.stop();">';
        } else if (this.running === -1) {
            sHtml += '<input type="button" value="继续采集" onclick="BeMonkey.continue();"> <input type="button" value="终止采集" onclick="BeMonkey.stop();">';
        }
        sHtml += '</div>';

        sHtml += '<div style="padding-top: 10px;">';
        sHtml += '当前操作：<span id="be-monkey-<?php echo $this->pullDriver->id; ?>-status"></span>';
        sHtml += "</div>";

        if (this.step === "link") {
            let percent = 0
            if (this.totalPageLinks > 0) {
                percent = Math.round((this.totalPageLinks - this.links.length) * 100 / this.totalPageLinks);
            }

            sHtml += '<div style="padding-top: 10px; display: flex;">';
            sHtml += '<div style="flex: 0 0 auto;">分页进度：</div>';
            sHtml += '<div style="flex: 1"><div style="margin-top: 5px; height: 10px; background-color: #eee"><div style="height: 10px; background-color: green; width: ' + percent + '%;"></div></div></div>';
            sHtml += '<div style="flex: 0 0 auto; padding-left: 10px;">' + (this.totalPageLinks - this.links.length) + '/'  + this.totalPageLinks + '</div>';
            sHtml += "</div>";
        }

        sHtml += '<div style="padding-top: 10px;">';
        sHtml += '采集总计：<strong>' + this.totalPages + '</strong> 分页, <strong>' + this.totalLinks + '</strong> 篇';
        sHtml += "</div>";

        if (this.step !== "init") {
            sHtml += '<div style="padding-top: 10px; display: flex;">';
            sHtml += '<div style="flex: 0 0 auto;">开始时间：</div>';
            sHtml += '<div style="flex: 0 0 auto;">' + this.startTime + '</div>';
            sHtml += "</div>";

            let diffSec;
            if (this.endTime !== "") {
                sHtml += '<div style="padding-top: 10px; display: flex;">';
                sHtml += '<div style="flex: 0 0 auto;">结束时间：</div>';
                sHtml += '<div style="flex: 0 0 auto;">' + this.endTime + '</div>';
                sHtml += "</div>";

                diffSec = new Date(this.endTime) - new Date(this.startTime);
            } else {
                diffSec = new Date() - new Date(this.startTime);
            }

            diffSec = Math.floor(diffSec / 1000);

            let diffStr = "";
            if (diffSec > 86400) {
                diffStr += Math.floor(diffSec / 86400) + "天 ";
                diffSec = diffSec % 86400;
            }

            if (diffSec > 3600) {
                diffStr += Math.floor(diffSec / 3600) + ' 小时 ';
                diffSec = diffSec % 3600;
            }

            if (diffSec > 60) {
                diffStr += Math.floor(diffSec / 60) + ' 分钟 ';
                diffSec = diffSec % 60;
            }

            if (diffSec > 0) {
                diffStr += diffSec + ' 秒';
            }

            sHtml += '<div style="padding-top: 10px; display: flex;">';
            sHtml += '<div style="flex: 0 0 auto;">累计用时：</div>';
            sHtml += '<div style="flex: 0 0 auto;">' + diffStr + '</div>';
            sHtml += "</div>";
        }

        for (let p in this.parkMap) {
            sHtml += '<a id="be-monkey-<?php echo $this->pullDriver->id; ?>-park-' + p + '" style="position: absolute; display: block; width: 10px; height: 10px; ';
            if (this.park === p) {
                sHtml += 'background-color: #00485b;';
            } else {
                sHtml += 'background-color: #ccc;';
            }
            sHtml += this.parkMap[p][0] + ': 0; '+ this.parkMap[p][1] + ': 0;';
            sHtml += '" href="javascript:void(0);" onclick="BeMonkey.changePark(\'' + p + '\');"></a>';
        }

        sHtml += '</div>';
        $('body').append(sHtml);
    },

    // 控制台停放位置
    changePark: function (p) {
        localStorage.setItem('be:monkey:park', p);

        let $e = $("#be-monkey-<?php echo $this->pullDriver->id; ?>");
        if (this.parkMap[p][0] === 'left') {
            $e.css('left', "10px");
            $e.css('right', "auto");
        } else {
            $e.css('left', "auto");
            $e.css('right', "10px");
        }

        if (this.parkMap[p][1] === 'top') {
            $e.css('top', "10px");
            $e.css('bottom', "auto");
        } else {
            $e.css('top', "auto");
            $e.css('bottom', "10px");
        }

        for (let pp in this.parkMap) {
            let $p = $("#be-monkey-<?php echo $this->pullDriver->id; ?>-park-" + pp);
            if (p === pp) {
                $p.css("background-color", "#00485b");
            } else {
                $p.css("background-color", "#ccc");
            }
        }
    },

    // 设置状态
    status: function (sHtml) {
        $("#be-monkey-<?php echo $this->pullDriver->id; ?>-status").html(sHtml);
    },


    // 采集分页页面
    processPage: function () {
        let nextPage = this.getNewNextPage();
        if (nextPage) {
            this.nextPage = nextPage;
            localStorage.setItem("be:monkey:nextPage", nextPage);
        } else {
            this.nextPage = false;
            localStorage.removeItem("be:monkey:nextPage");
        }

        this.status("采集当前页面中的链接...");

        let links = this.getLinks();

        this.totalPages++;
        localStorage.setItem("be:monkey:totalPages", this.totalPages);

        if (links.length > 0) {
            this.links = links;
            localStorage.setItem('be:monkey:links', links.join("|"));

            this.totalPageLinks = links.length;
            localStorage.setItem("be:monkey:totalPageLinks", this.totalPageLinks);

            this.step = "link";
            localStorage.setItem("be:monkey:step", "link");

            this.status("采集到 " + links.length +" 个链接，即将遍历");

            setTimeout(function () {
                window.location.href = links[0];
            }, <?php echo $this->pullDriver->interval; ?>);
        } else {
            this.totalPageLinks = 0;
            localStorage.setItem("be:monkey:totalPageLinks", 0);

            if (this.nextPage) {
                this.currentPage = this.nextPage;
                localStorage.setItem("be:monkey:currentPage", this.currentPage);

                this.nextPage = false;
                localStorage.removeItem("be:monkey:nextPage");

                this.step = "page";
                localStorage.setItem("be:monkey:step", "page");

                // 分页页面未采集到链接
                this.status("未采集到链接，跑转到下页...");

                let _this = this;
                setTimeout(function () {
                    window.location.href = _this.currentPage;
                }, <?php echo $this->pullDriver->interval; ?>);
            } else {
                this.complete();
            }
        }
    },

    // 采集链接页面
    processLink: function () {

        this.status("采集链接内容...");

        if (this.links.length > 0) {

            let postDataFields = [];
            <?php
            foreach ($this->pullDriver->fields as $key => $field) {?>
            postDataFields.push({
                name: "<?php echo $field['name']; ?>",
                content: this.getField_<?php echo $key; ?>()
            });
            <?php } ?>

            let postData = {
                pull_driver_id: "<?php echo $this->pullDriver->id; ?>",
                url: window.location.href,
                fields: postDataFields
            };

            let _this = this;

            // 上传文件到账单系统
            GM_xmlhttpRequest({
                method: "POST",
                url: "<?php echo beUrl('Monkey.Content.receive'); ?>",
                data: JSON.stringify(postData),
                headers: {
                    "Content-Type": "application/json"
                },
                responseType: "json",
                onload: function (response) {

                    if (response.status !== 200) {
                        console.log(response);

                        _this.status("提交数据失败，60秒后再次尝试！");

                        setTimeout(function () {
                            window.location.reload();
                        }, 60000);

                        return;
                    }

                    if (!response.response) {
                        console.log(response);

                        _this.status("提交数据失败（无有效返回），60秒后再次尝试！");

                        setTimeout(function () {
                            window.location.reload();
                        }, 60000);

                        return;
                    }

                    if (!response.response.success) {
                        console.log(response);

                        let message = "";
                        if (response.response.message) {
                            message = response.response.message;
                        }
                        _this.status("提交数据失败（" + message + "），60秒后再次尝试！");

                        setTimeout(function () {
                            window.location.reload();
                        }, 60000);

                        return;
                    }

                    // 采集成功后将链接从列表中移除
                    _this.links.shift();
                    localStorage.setItem('be:monkey:links', _this.links.join("|"));

                    _this.totalLinks++;
                    localStorage.setItem("be:monkey:totalLinks", _this.totalLinks);

                    if (_this.links.length > 0) {
                        _this.step = "link";
                        localStorage.setItem("be:monkey:step", "link");

                        _this.status("当前链接采集完成");

                        setTimeout(function () {
                            window.location.href = _this.links[0];
                        }, <?php echo $this->pullDriver->interval; ?>);
                    } else {

                        if (_this.nextPage) {
                            _this.currentPage = _this.nextPage;
                            localStorage.setItem("be:monkey:currentPage", _this.currentPage);

                            _this.nextPage = false;
                            localStorage.removeItem("be:monkey:nextPage");

                            _this.step = "page";
                            localStorage.setItem("be:monkey:step", "page");

                            // 分页页面未采集到链接
                            _this.status("当前分页所有链接采集完成，前往下页...");

                            setTimeout(function () {
                                window.location.href = _this.currentPage;
                            }, <?php echo $this->pullDriver->interval; ?>);
                        } else {
                            _this.complete();
                        }
                    }
                }
            });
        } else {
            if (this.nextPage) {
                this.currentPage = this.nextPage;
                localStorage.setItem("be:monkey:currentPage", this.currentPage);

                this.nextPage = false;
                localStorage.removeItem("be:monkey:nextPage");

                this.step = "page";
                localStorage.setItem("be:monkey:step", "page");

                // 分页页面未采集到链接
                this.status("当前分页所有链接采集完成，前往下页...");

                let _this = this;
                setTimeout(function () {
                    window.location.href = _this.currentPage;
                }, <?php echo $this->pullDriver->interval; ?>);
            } else {
                this.complete();
            }
        }
    },

    start: function () {
        this.currentPage = "<?php echo $this->pullDriver->start_page; ?>";
        localStorage.setItem("be:monkey:currentPage", this.currentPage);

        this.nextPage = false;
        localStorage.removeItem("be:monkey:nextPage");

        this.running = 1;
        localStorage.setItem("be:monkey:running", 1);

        this.step = "page";
        localStorage.setItem("be:monkey:step", "page");

        this.totalPageLinks = 0;
        localStorage.setItem("be:monkey:totalPageLinks", 0);

        this.totalLinks = 0;
        localStorage.setItem("be:monkey:totalLinks", 0);

        this.totalPages = 0;
        localStorage.setItem("be:monkey:totalPages", 0);

        this.startTime = this.getDateTime();
        localStorage.setItem("be:monkey:startTime", this.startTime);

        this.endTime = "";
        localStorage.setItem("be:monkey:endTime", "");

        // 跳转到开始页
        window.location.href = this.currentPage;
    },

    pause: function () {
        this.running = -1;
        localStorage.setItem("be:monkey:running", -1);

        window.location.reload();
    },

    continue: function () {
        this.running = 1;
        localStorage.setItem("be:monkey:running", 1);

        window.location.reload();
    },

    stop: function () {
        this.running = 0;
        localStorage.setItem("be:monkey:running", 0);

        this.step = "init";
        localStorage.setItem("be:monkey:step", "init");

        window.location.reload();
    },

    complete: function () {
        this.running = 0;
        localStorage.setItem("be:monkey:running", 0);

        this.step = "complete";
        localStorage.setItem("be:monkey:step", "complete");

        this.endTime = this.getDateTime();
        localStorage.setItem("be:monkey:endTime", this.endTime);

        window.location.reload();
    },


    base64encode: function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;
        var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

        input = input.replace(/\r\n/g, "\n");
        var utftext = "";
        for (var n = 0; n < input.length; n++) {
            var c = input.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        input = utftext;
        while (i < input.length) {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;
            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }
            output = output +
                _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
                _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
        }
        return output;
    },

    getDateTime: function () {
        var date = new Date(),
            Y = date.getFullYear() + '',
            M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1),
            D = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate()),
            h = (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()),
            m = (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes()),
            s = (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
        return Y + "-" + M + "-" + D + " " + h + ":" + m + ":" + s
    }
};

$(function () {
    BeMonkey.init();
});

<?php if (false) { ?>
</script>
<?php } ?>
</be-html>
