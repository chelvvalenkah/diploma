(function ($) {
    $.fn.eventouchcalendar = function (params, conf_events) {
        function getWeek(date) {
            var daysArray = Array();
            var date = date || -1;
            var today;
            if (date == -1) {
                today = new Date()
            } else {
                today = new Date(date)
            }
            var dayIndexFromWeek = today.getDay();
            today.addDays(-dayIndexFromWeek);
            firstDay = new Date(today.toString());
            firstDay.addDays(1);
            for (var i = 0; i < 7; i++) {
                daysArray.push(today.addDays(1).getDate() + '/' + (today.getMonth() + 1));
                var monthTwoNumbers = (today.getMonth() + 1) < 10 ? '0' + (today.getMonth() + 1) : (today.getMonth() + 1);
                var dayTwoNumbers = today.getDate() < 10 ? '0' + today.getDate() : today.getDate();
                var dateYearMonthDay = today.getFullYear() + '-' + monthTwoNumbers + '-' + dayTwoNumbers;
                $("#agenda tr td").eq(i).attr("data-date", dateYearMonthDay)
            }
            lastDay = new Date(today.toString());
            return daysArray
        }
        function fillTh(th, isCreation) {
            var isCreation = isCreation || false;
            if (isCreation) {
                th.appendChild(document.createTextNode(daysList[i] + ' ' + daysNum[i]));
                $("#agenda thead tr").append(th)
            } else {
                th.each(function (index, element) {
                    $(element).html((document.createTextNode(daysList[index] + ' ' + daysNum[index])))
                })
            }
        }
        function readEvents() {
            $(".hourSelected").hide();
            var $tmpTd = $("#agenda tbody tr td");
            $.each(eventList, function (key, value) {
                $tmpTd.each(function () {
                    if ($(this).attr("data-date") == value[0]) {
                        //value[1].show();
						value[1].css('display', 'block');
                        return false
                    }
                })
            })
        }
        function eltSelection(event, ui) {
            if (ui.selected.nodeName == "SPAN") {
                lastSelectedElt.push($(ui.selected))
            }
        }
		
		function createEvents() {
			conf_events.forEach(function(conf_event) {
			/*for (var conf_event in conf_events) {*/
				var groupLength = conf_event.length;
				var $columnId = $("#agenda tbody tr td").index($('td[data-date="'+conf_event.date+'"]'));
				var evtHeight = groupLength * (params.cell_height + BORDER_CELL_LENGTH) - 2 * BORDER_CELL_LENGTH;
				var evtTop = $('#agenda tbody tr td').eq($columnId).find('span[data-time="'+conf_event.start+'"]').offset().top;
				var evtBottom = evtTop + evtHeight;
				var evtLeft = TAB_LEFT + ($columnId * (params.cell_width + BORDER_CELL_LENGTH)) + 1;
				if ($.browser.msie && parseInt($.browser.version, 10) === 9) {
					evtLeft -= 1
				}
				var boolCollision = false;
				$("#agenda tbody tr td:eq(" + $columnId + ") div:not(.ui-selectee)").each(function () {
					var evtOldStart = $(this).offset().top;
					var evtOldEnd = evtOldStart + $(this).height();
					if ((evtBottom >= evtOldStart && evtOldEnd >= evtTop) || (evtOldEnd >= evtTop && evtBottom >= evtOldStart) || (evtBottom <= evtOldEnd && evtOldStart <= evtTop) || (evtOldEnd <= evtBottom && evtTop <= evtOldStart)) {
						boolCollision = true;
						return false
					}
				});
				if (!boolCollision) {
					var evt = document.createElement("div");
					evt.className = "hourSelected";
					$(evt).attr("data-start", conf_event.start);
					$(evt).attr("data-duration", conf_event.length);
					$(evt).attr("data-color", conf_event.color);
					$(evt).css("backgroundColor", '#'+conf_event.color);
					$(evt).css({
						width: params.cell_width,
						height: evtHeight,
						top: evtTop,
						left: evtLeft-2 /* "-2" = костыль; возможно, из-за Bootstrap'а */
					});
					var evt_lnk = document.createElement("a");
					evt_lnk.className = 'div-link';
					evt_lnk.href = conf_event.href;
					$(evt_lnk).append(evt);
					$(evt_lnk).click(function() {
						location.href = this.href;
					});
					$("#agenda tbody tr td:eq(" + $columnId + ")").append(evt_lnk);
					$(evt).children(".ui-resizable-handle").css("width", params.cell_width);
					$(evt).attr("data-id", currentId++);
					eventList[eventList.length] = new Array(conf_event.date, $(evt));
				}
				
				var evtName = document.createElement("div");
				evtName.className = "evtName";
				var evt_height = $(evt).css("height");
				$(evtName).css({
					height: evt_height,
					width: params.cell_width,
					lineHeight: evt_height
				});
				if (conf_event.name.length > DISPLAYED_CHAR_COUNT) {
					evtName.appendChild(document.createTextNode(conf_event.name.substring(0, DISPLAYED_CHAR_COUNT) + ".."));
					evtName.title = conf_event.name
				} else {
					evtName.appendChild(document.createTextNode(conf_event.name))
				}
				if (parseInt(evt_height, 10) <= 8) {
					$(evtName).hide()
				}
				var evtDesc = document.createElement("div");
				evtDesc.className = "evtDesc";
				$(evtDesc).css("display", "none");
				evtDesc.appendChild(document.createTextNode(conf_event.desc));
				$(evt).css({
					backgroundColor: conf_event.color,
					borderTopColor: conf_event.color
				});
				$(evt).append(evtName);
				$(evt).append(evtDesc);
			});
            highlightEvents();
		}
		
        function selectionEnd(event, ui) {
            /*
			if (lastSelectedElt.length > 0) {
                var groupLength = lastSelectedElt.length;
                var $columnId = $("#agenda tbody tr td").index(event.target);
                var evtHeight = groupLength * (params.cell_height + BORDER_CELL_LENGTH) - 2 * BORDER_CELL_LENGTH;
                var evtTop = $(lastSelectedElt[0]).offset().top;
                var evtBottom = evtTop + evtHeight;
                var evtLeft = TAB_LEFT + ($columnId * (params.cell_width + BORDER_CELL_LENGTH)) + 1;
                if ($.browser.msie && parseInt($.browser.version, 10) === 9) {
                    evtLeft -= 1
                }
                var boolCollision = false;
                $("#agenda tbody tr td:eq(" + $columnId + ") div:not(.ui-selectee)").each(function () {
                    var evtOldStart = $(this).offset().top;
                    var evtOldEnd = evtOldStart + $(this).height();
                    if ((evtBottom >= evtOldStart && evtOldEnd >= evtTop) || (evtOldEnd >= evtTop && evtBottom >= evtOldStart) || (evtBottom <= evtOldEnd && evtOldStart <= evtTop) || (evtOldEnd <= evtBottom && evtTop <= evtOldStart)) {
                        boolCollision = true;
                        return false
                    }
                });
                if (!boolCollision) {
                    var evt = document.createElement("div");
                    evt.className = "hourSelected";
                    $(evt).attr("data-start", $(lastSelectedElt[0]).attr("data-time"));
                    $(evt).attr("data-duration", lastSelectedElt.length);
                    $(evt).css({
                        width: params.cell_width,
                        height: evtHeight,
                        top: evtTop,
                        left: evtLeft
                    });
                    $("#agenda tbody tr td:eq(" + $columnId + ")").append(evt);
                    $lastCreatedElt = $(evt);
                    Shadowbox.open({
                        content: $("#formCreationEvt").html(),
                        title: "Event properties",
                        player: "html",
                        width: 360,
                        height: 325
                    });
                    $(evt).draggable({
                        containment: "#agenda tbody tr",
                        grid: [params.cell_width + BORDER_CELL_LENGTH, params.cell_height + BORDER_CELL_LENGTH],
                        start: dragStart,
                        stop: dragStop
                    }).droppable({
                        tolerance: "touch",
                        drop: function (event, ui) {
                            $(ui.draggable).draggable("disable");
                            isDropped = true;
                            $(ui.draggable).animate({
                                left: lastEvtPosX,
                                top: lastEvtPosY
                            }, "fast", function () {
                                $(ui.draggable).draggable("enable")
                            })
                        }
                    }).resizable({
                        containment: "#fixResize",
                        handles: "s",
                        minHeight: EVT_MIN_HEIGHT,
                        grid: [0, params.cell_height + BORDER_CELL_LENGTH],
                        resize: function (event, ui) {
                            ui.element.children(".evtName").fadeOut("fast")
                        },
                        stop: function (event, ui) {
                            var isCollide = false;
                            var oldEvtHeight = ui.originalSize.height;
                            var evtTop = ui.position.top;
                            var evtHeight = ui.size.height;
                            var evtBottom = evtTop + evtHeight;
                            ui.element.parent().children("div:not(.ui-selectee)").not(ui.element).each(function () {
                                var tmpTop = $(this).offset().top;
                                if (evtTop < tmpTop && evtBottom >= tmpTop) {
                                    isCollide = true;
                                    ui.element.animate({
                                        height: oldEvtHeight
                                    }, "fast", function () {
                                        if (ui.element.height() > EVT_MIN_HEIGHT) {
                                            ui.element.children(".evtName").fadeIn("fast")
                                        }
                                    });
                                    return false
                                }
                            });
                            if (!isCollide) {
                                if (ui.element.height() > EVT_MIN_HEIGHT) {
                                    ui.element.children(".evtName").fadeIn("fast")
                                }
                                ui.element.children(".evtName").css({
                                    height: ui.element.css("height"),
                                    lineHeight: ui.element.css("height")
                                });
                                var durationNew = 0;
                                var tmpHeight = ui.element.height();
                                if (tmpHeight == EVT_MIN_HEIGHT) {
                                    durationNew = 1
                                } else {
                                    durationNew = (tmpHeight - EVT_MIN_HEIGHT) / (params.cell_height + BORDER_CELL_LENGTH) + 1
                                }
                                if (durationNew != ui.element.attr("data-duration")) {
                                    ui.element.attr("data-duration", durationNew)
                                }
                            }
                        }
                    }).dblclick(function () {
                        isModif = true;
                        $modifElt = $(evt);
                        $("#delCreationEvt").attr("disabled", false);
                        Shadowbox.open({
                            content: $("#formCreationEvt").html(),
                            title: "Event properties",
                            player: "html",
                            width: 360,
                            height: 325
                        })
                    });
                    $(evt).children(".ui-resizable-handle").css("width", params.cell_width);
                    lastColumnId = $("#agenda tbody tr td:eq(" + $columnId + ")").attr("data-date")
                }
                lastSelectedElt = new Array()
            }
            $("body").css("overflow", "visible")
			*/
        }
        function dragStart(event, ui) {
            lastEvtPosX = ui.offset.left;
            lastEvtPosY = ui.offset.top;
            columnDayStart = ui.helper.parent()
        }
        function dragStop(event, ui) {
            if (!isDropped) {
                var evt = ui.helper;
                var evtLeft = evt.offset().left;
                var evtRight = evtLeft + evt.width();
                var columnDate = -1;
                var idColumn = -1;
                $("#agenda tbody tr td").each(function (index) {
                    var tdLeft = $(this).offset().left;
                    var tdRight = tdLeft + $(this).width();
                    if (tdRight >= evtRight && evtLeft >= tdLeft) {
                        $(this).append(evt);
                        columnDate = $("#agenda tbody tr td:eq(" + index + ")").attr("data-date");
                        idColumn = index;
                        return false
                    }
                });
                var testDate = new Date();
                testDate.setSeconds(0);
                testDate.setMinutes($(evt).attr("data-start").split(":")[1]);
                testDate.setHours($(evt).attr("data-start").split(":")[0]);
                testDate.addMinutes(((ui.offset.top - lastEvtPosY) / (params.cell_height + BORDER_CELL_LENGTH)) * 30);
                var tmpDate = $(evt).parent().attr("data-date");
                var tmpHour = (testDate.getHours() < 10 ? '0' + testDate.getHours() : testDate.getHours()) + ':' + (testDate.getMinutes() < 10 ? '0' + testDate.getMinutes() : testDate.getMinutes());
                $(evt).attr("data-start", tmpHour);
                $.each(eventList, function (key, value) {
                    if ($(value[1]).attr('data-id') == $(evt).attr('data-id')) {
                        value[0] = columnDate;
                        return false
                    }
                });
                highlightEvents()
            } else {
                isDropped = false
            }
        }
        function limitsChange(event, ui) {
            params.hour_mask[MASK_START] = HOUR_END - parseInt(ui.values[1], 10);
            params.hour_mask[MASK_END] = HOUR_END - parseInt(ui.values[0], 10);
            LINES_NUMBER = (params.hour_mask[MASK_END] - params.hour_mask[MASK_START]) * 4;
            $("#topMask").css("height", params.hour_mask[MASK_START] * ((params.cell_height + BORDER_CELL_LENGTH) * 4));
            $("#bottomMask").css("height", (HOUR_END - params.hour_mask[MASK_END] - HOUR_START) * ((params.cell_height + BORDER_CELL_LENGTH) * 4));
            $("#bottomMask").offset({
                top: TAB_BOTTOM - (HOUR_END - HOUR_START - params.hour_mask[MASK_END]) * ((params.cell_height + BORDER_CELL_LENGTH) * 4)
            })
        }
        function limitsChangeHorizontal(event, ui) {
            params.day_mask[MASK_START] = parseInt(ui.values[0], 10);
            params.day_mask[MASK_END] = parseInt(ui.values[1], 10);
            $("#leftMask").css("width", params.day_mask[MASK_START] * (params.cell_width + BORDER_CELL_LENGTH));
            $("#rightMask").css("width", (DAYS_NUMBER - params.day_mask[MASK_END]) * (params.cell_width + BORDER_CELL_LENGTH));
            $("#rightMask").offset({
                left: ((TAB_LEFT + TAB_WIDTH) - (((DAYS_NUMBER - params.day_mask[MASK_END]) * (params.cell_width + BORDER_CELL_LENGTH))))
            })
        }
        function initCSS() {
            $("#hoursList tr").css("height", (params.cell_height + BORDER_CELL_LENGTH) * 2);
            $("#hoursList").css({
                width: HOURS_LIST_WIDTH,
                marginRight: HOURS_LIST_MARGIN_RIGHT,
                marginTop: TH_HEIGHT - 6,
                lineHeight: HOUR_LIST_LINE_HEIGHT+'px'
            });
            $(".ui-slider-vertical").css("height", DIV_HEIGHT);
            $(".ui-slider-horizontal").css("width", TAB_WIDTH);
            $("#limitsChoice").css({
                marginTop: TH_HEIGHT + TH_BORDER_LENGTH + 2,
                marginRight: SLIDER_VERTICAL_MARGIN_RIGHT,
                marginBottom: 0,
                marginLeft: 0
            });
            $("#limitsChoiceHorizontal.ui-widget-content").css("width", TAB_WIDTH);
            $("#limitsChoiceHorizontal").css({
                marginTop: SLIDER_HORIZONTAL_MARGIN_TOP,
                marginLeft: SLIDER_VERTICAL_MARGIN_RIGHT,/*TAB_LEFT - HOURS_LIST_LEFT - 6*/
				marginRight: -SLIDER_VERTICAL_MARGIN_RIGHT //added
            });
            $("#agenda th").css({
                width: params.cell_width,
                height: TH_HEIGHT,
                borderBottomWidth: TH_BORDER_LENGTH
            });
            $("#agenda tr td div:not(.hourSelected)").css({
                width: params.cell_width,
                height: DIV_HEIGHT,
                borderLeft: BORDER_CELL_LENGTH + "px solid #CCC"
            });
            $(".hour").css({
                width: params.cell_width,
                height: params.cell_height,
                borderTopWidth: BORDER_CELL_LENGTH
            });
            $(".hourZebra").css("borderTopWidth", BORDER_CELL_LENGTH);
            $("#fixResize").css({
                styleFloat: "left",
                height: DIV_HEIGHT,
                paddingTop: TAB_TOP + TH_HEIGHT + BORDER_CELL_LENGTH,
                display: 'none'
            });
            $("#topMask").css({
                width: TAB_WIDTH,
                height: (params.hour_mask[MASK_START] - HOUR_START) * ((params.cell_height + BORDER_CELL_LENGTH) * 4),
                left: TAB_LEFT,
                top: TAB_TOP + TH_HEIGHT + TH_BORDER_LENGTH
            });
            $("#bottomMask").css({
                width: TAB_WIDTH,
                height: (HOUR_END - params.hour_mask[MASK_END]) * ((params.cell_height + BORDER_CELL_LENGTH) * 4),
                left: TAB_LEFT,
                top: TAB_BOTTOM - ((HOUR_END - params.hour_mask[MASK_END]) * ((params.cell_height + BORDER_CELL_LENGTH) * 4))
            });
            $("#leftMask").css({
                width: params.day_mask[MASK_START] * (params.cell_width + BORDER_CELL_LENGTH),
                height: DIV_HEIGHT,
                left: TAB_LEFT,
                top: TAB_TOP + TH_HEIGHT + BORDER_CELL_LENGTH
            });
            $("#rightMask").css({
                width: (DAYS_NUMBER - params.day_mask[MASK_END]) * (params.cell_width + BORDER_CELL_LENGTH),
                height: DIV_HEIGHT,
                left: (TAB_LEFT + TAB_WIDTH) - ((DAYS_NUMBER - params.day_mask[MASK_END]) * (params.cell_width + BORDER_CELL_LENGTH)),
                top: TAB_TOP + TH_HEIGHT + BORDER_CELL_LENGTH
            });
            $(".ui-selecting").css("borderTopColor", "#569DE8");
            $("#nav").css("marginLeft", HOURS_LIST_WIDTH + HOURS_LIST_MARGIN_RIGHT + SLIDER_VERTICAL_MARGIN_RIGHT + SLIDER_WIDTH + BORDER_CELL_LENGTH + SLIDER_OFFSET);
            $("#agenda").parent().css("width", TAB_LEFT + TAB_WIDTH - HOURS_LIST_LEFT + 1);
            if ($.browser.msie && parseInt($.browser.version, 10) === 8) {
                $("#topMask").css("top", parseInt($("#topMask").css("top"), 10) + 1);
                $("#bottomMask").css("top", parseInt($("#bottomMask").css("top"), 10) + 1);
                $("#leftMask").css("top", parseInt($("#leftMask").css("top"), 10) + 1);
                $("#rightMask").css("top", parseInt($("#rightMask").css("top"), 10) + 1)
            }
        }
        function miniMonthCalendar(date) {
            $('#miniMonthCalendar').children().remove();
            $('#currentMonth').contents().remove();
            date = date || new Date();
            var allDays = new Array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            var firstDayOfMonth = new Date(date.toString()).moveToFirstDayOfMonth();
            var lastDayNumberOfMonth = new Date(date.toString()).moveToLastDayOfMonth().getDate();
            var tmpDay = firstDayOfMonth.clone();
            $("#currentMonth").append(document.createTextNode(allMonths[tmpDay.getMonth()] + ' ' + tmpDay.getFullYear()));
            $('#miniMonthCalendar').append(document.createElement('tr'));
            $('#miniMonthCalendar').append(document.createElement('tr'));
            for (var i = 1; i <= lastDayNumberOfMonth; i++) {
                var dayName = allDays[tmpDay.getDay()].substring(0, 2);
                var dayNumber = tmpDay.getDate();
                var dayName_td = document.createElement('td');
                dayName_td.appendChild(document.createTextNode(dayName));
                var dayNumber_td = document.createElement('td');
                dayNumber_td.appendChild(document.createTextNode((dayNumber < 10) ? '0' + dayNumber : dayNumber));
                $(dayNumber_td).click(function () {
                    daysNum = getWeek(new Date(date.getFullYear(), date.getMonth(), $(this).text()));
                    fillTh($("#agenda tr th"));
                    readEvents()
                });
                $('#miniMonthCalendar tr').eq(0).append(dayName_td);
                $('#miniMonthCalendar tr').eq(1).append(dayNumber_td);
                tmpDay.addDays(1)
            }
            highlightEvents()
        }
        function highlightEvents() {
            if (eventList != undefined) {
                $('#miniMonthCalendar tr').eq(1).children().removeClass('isEvent');
                var miniCalendarYear = $('#monthManagement #currentMonth').text().split(' ')[1];
                var miniCalendarMonth = $('#monthManagement #currentMonth').text().split(' ')[0];
                $(allMonths).each(function (i) {
                    if (allMonths[i] == miniCalendarMonth) {
                        miniCalendarMonth = i + 1;
                        return false
                    }
                });
                $.each(eventList, function (key, value) {
                    $('#miniMonthCalendar tr').eq(1).children().each(function () {
                        if (value[0].split('-')[0] == miniCalendarYear && value[0].split('-')[1] == miniCalendarMonth) {
                            if (value[1].filter(":visible") && value[0].split("-")[2] == $(this).text()) {
                                $(this).addClass('isEvent');
                                return false
                            }
                        }
                    })
                })
            }
        }
		
		var BORDER_CELL_LENGTH = 1;
		var HOURS_LIST_LEFT = $("#hoursList").offset().left;
		var HOURS_LIST_WIDTH = 18;
		var HOURS_LIST_MARGIN_RIGHT = 20;
        var HOUR_LIST_LINE_HEIGHT = 16;
		var SLIDER_VERTICAL_MARGIN_RIGHT = 30;
        var SLIDER_WIDTH = 2;
		var SLIDER_OFFSET = 3;
		var TAB_LEFT = HOURS_LIST_LEFT + HOURS_LIST_WIDTH + HOURS_LIST_MARGIN_RIGHT + SLIDER_VERTICAL_MARGIN_RIGHT + SLIDER_WIDTH + BORDER_CELL_LENGTH + SLIDER_OFFSET;
		
        var MASK_START = 0,
            MASK_END = 1;
        var CELL_MIN_WIDTH = 30,
            CELL_MIN_HEIGHT = 7;
		var client_w=(1260 - HOURS_LIST_LEFT - HOURS_LIST_WIDTH - HOURS_LIST_MARGIN_RIGHT - SLIDER_VERTICAL_MARGIN_RIGHT - SLIDER_WIDTH - BORDER_CELL_LENGTH - SLIDER_OFFSET)/7;
        var defaults = {
            hour_mask: new Array(8, 20),
            day_mask: new Array(1, 7),
            cell_width: 150,
            cell_height: 9
        };
        var params = $.extend(defaults, params);
        params.cell_width = (params.cell_width < CELL_MIN_WIDTH) ? CELL_MIN_WIDTH : params.cell_width;
        params.cell_height = (params.cell_height < CELL_MIN_HEIGHT) ? CELL_MIN_HEIGHT : params.cell_height;
        var allMonths = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        var date = new Date();
        var $monthManagement = $('#monthManagement');
        $monthManagement.children('#prevYear').click(function () {
            miniMonthCalendar(date.moveToFirstDayOfMonth().addYears(-1))
        });
        $monthManagement.children('#nextYear').click(function () {
            miniMonthCalendar(date.moveToFirstDayOfMonth().addYears(1))
        });
        $monthManagement.children('#prevMonth').click(function () {
            miniMonthCalendar(date.moveToFirstDayOfMonth().addMonths(-1))
        });
        $monthManagement.children('#nextMonth').click(function () {
            miniMonthCalendar(date.moveToFirstDayOfMonth().addMonths(1))
        });
        miniMonthCalendar();
		
        var HOUR_START = 6;
        var HOUR_END = 24;
        var HOURS_NUMBER = HOUR_END - HOUR_START;
        var LINES_NUMBER = HOURS_NUMBER * 4;
        var DAY_START = 0;
        var DAY_END = 7;
        var DAYS_NUMBER = DAY_END - DAY_START;
        /*var BORDER_CELL_LENGTH = 1;*/
        var DIV_HEIGHT = LINES_NUMBER * (params.cell_height + BORDER_CELL_LENGTH);
        var EVT_MIN_HEIGHT = params.cell_height - BORDER_CELL_LENGTH;
        var DISPLAYED_CHAR_COUNT = 15;
        /*var HOURS_LIST_LEFT = $("#hoursList").offset().left;
        var HOURS_LIST_WIDTH = 18;
        var HOURS_LIST_MARGIN_RIGHT = 20;
        var SLIDER_VERTICAL_MARGIN_RIGHT = 30;
        var SLIDER_WIDTH = 2;*/
        var SLIDER_HORIZONTAL_MARGIN_TOP = 25;
        /*var SLIDER_OFFSET = 3;*/
        var TH_BORDER_LENGTH = 1;
        var TH_HEIGHT = TH_BORDER_LENGTH + 39;
        var TAB_WIDTH = (params.cell_width + BORDER_CELL_LENGTH) * DAYS_NUMBER;
        /*var TAB_LEFT = HOURS_LIST_LEFT + HOURS_LIST_WIDTH + HOURS_LIST_MARGIN_RIGHT + SLIDER_VERTICAL_MARGIN_RIGHT + SLIDER_WIDTH + BORDER_CELL_LENGTH + SLIDER_OFFSET;*/
        var TAB_TOP = $("#agenda").offset().top;
        var TAB_BOTTOM = TAB_TOP + TH_HEIGHT + BORDER_CELL_LENGTH + LINES_NUMBER * (params.cell_height + BORDER_CELL_LENGTH);
        var $hoursList = $("#hoursList");
        var lastSelectedElt = new Array();
        var eventList = new Array();
        var currentId = 0;
        var lastColumnId = -1;
        var lastEvtPosX = -1;
        var lastEvtPosY = -1;
        var columnDayStart = -1;
        var isDropped = false;
        var $lastCreatedElt = false;
        var $modifElt = false;
        var isModif = false;
        var daysList = Array("ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ", "ВС");
        var daysNum = getWeek();
        var firstDay;
        var lastDay;
		
        Shadowbox.init({
            modal: true,
            enableKeys: false,
            resizeDuration: 0.1,
            onFinish: function () {
                $("#sb-container #colorChoose").miniColors({
                    letterCase: 'uppercase'
                });
                if (isModif) {
                    $("#sb-container #name").val($modifElt.children(".evtName").text());
                    $("#sb-container #desc").val($modifElt.children(".evtDesc").text());
                    $("#sb-container #colorChoose").miniColors('value', $modifElt.attr("data-color"))
                }
                $("#sb-container #valCreationEvt").click(function () {
                    if ($("#sb-container #name").val().length > 0) {
                        if (!isModif) {
                            var $form_name = $.trim($("#sb-container #name").val()).replace(/\s+/gi, ' ');
                            var $form_desc = $.trim($("#sb-container #desc").val()).replace(/\s+/gi, ' ');
                            var $form_color = $("#sb-container #colorChoose").val();
                            var $evt_height = $lastCreatedElt.css("height");
                            var evtName = document.createElement("div");
                            evtName.className = "evtName";
                            $(evtName).css({
                                height: $evt_height,
                                width: params.cell_width,
                                lineHeight: $evt_height
                            });
                            if ($form_name.length > DISPLAYED_CHAR_COUNT) {
                                evtName.appendChild(document.createTextNode($form_name.substring(0, DISPLAYED_CHAR_COUNT) + ".."));
                                evtName.title = $form_name
                            } else {
                                evtName.appendChild(document.createTextNode($form_name))
                            }
                            if (parseInt($evt_height, 10) <= 8) {
                                $(evtName).hide()
                            }
                            var evtDesc = document.createElement("div");
                            evtDesc.className = "evtDesc";
                            $(evtDesc).css("display", "none");
                            evtDesc.appendChild(document.createTextNode($form_desc));
                            $lastCreatedElt.css({
                                backgroundColor: $form_color,
                                borderTopColor: $form_color
                            });
                            $lastCreatedElt.append(evtName);
                            $lastCreatedElt.append(evtDesc);
                            Shadowbox.close();
                            var dbTime = $lastCreatedElt.parent().attr("data-date") + ' ' + $lastCreatedElt.attr("data-start") + ':00';
                            $lastCreatedElt.attr("data-color", $form_color.substring(1));
                            $lastCreatedElt.attr("data-id", currentId++);
                            eventList[eventList.length] = new Array(lastColumnId, $lastCreatedElt);
                            highlightEvents()
                        } else {
                            var $form_name = $.trim($("#sb-container #name").val()).replace(/\s+/gi, ' ');
                            var $form_desc = $.trim($("#sb-container #desc").val()).replace(/\s+/gi, ' ');
                            var $form_color = $("#sb-container #colorChoose").val();
                            if ($form_name.length > DISPLAYED_CHAR_COUNT) {
                                $modifElt.children(".evtName").text($form_name.substring(0, DISPLAYED_CHAR_COUNT) + "..")
                            } else {
                                $modifElt.children(".evtName").text($form_name)
                            }
                            $modifElt.children(".evtDesc").text($form_desc);
                            $modifElt.css("backgroundColor", $form_color);
                            $modifElt.css("borderTopColor", $form_color);
                            Shadowbox.close();
                            isModif = false;
                            $lastCreatedElt.attr("data-color", $form_color.substring(1))
                        }
                    } else {
                        $("#sb-container #name").addClass("error")
                    }
                    $("#delCreationEvt").attr("disabled", true)
                });
                $("#sb-container #cancelCreationEvt").click(function () {
                    if (!isModif) {
                        $lastCreatedElt.remove()
                    } else {
                        isModif = false
                    }
                    $("#delCreationEvt").attr("disabled", true);
                    Shadowbox.close()
                });
                $("#sb-container #delCreationEvt").click(function () {
                    isModif = false;
                    var deleteId = $modifElt.attr("data-id");
                    $.each(eventList, function (key, value) {
                        if (value[1].attr('data-id') == deleteId) {
                            eventList.splice(key, 1);
                            return false
                        }
                    });
                    $modifElt.remove();
                    $("#delCreationEvt").attr("disabled", true);
                    Shadowbox.close();
                    highlightEvents()
                })
            }
        });
        for (var i = HOUR_START; i <= HOUR_END; i+=0.5) {
            var tr = document.createElement("tr");
            var td = document.createElement("td");
            //td.appendChild(document.createTextNode((i|0) + "h"));
			if (i % 1 == 0.5) {
                    td.appendChild(document.createTextNode(""));
			}
			else td.appendChild(document.createTextNode((i|0) + "h"));	
            tr.appendChild(td);
            $hoursList.append(tr)
        }
        for (var i = DAY_START; i < DAY_END; i++) {
            var th = document.createElement("th");
            var td = document.createElement("td");
            var div = document.createElement("div");
            fillTh(th, true);
            $(div).attr("id", daysList[i]);
            td.appendChild(div);
            var $day = $(td);
            var tmpHour = new Date();
            tmpHour.setSeconds(0);
            tmpHour.setMinutes(0);
            tmpHour.setHours(HOUR_START);
            for (var j = 0; j < LINES_NUMBER; j++) {
                var span = document.createElement("span");
                span.className = "hour";
                var tmpStrHours = tmpHour.getHours() < 10 ? '0' + tmpHour.getHours() : tmpHour.getHours();
                var tmpStrMinutes = tmpHour.getMinutes() < 10 ? '0' + tmpHour.getMinutes() : tmpHour.getMinutes();
                $(span).attr("data-time", tmpStrHours + ':' + tmpStrMinutes);
                tmpHour.addMinutes(15);
                if (j % 2 == 1) {
                    span.className += " hourZebra"
                }
                $day.append(span)
            }
            $day.selectable({
                filter: ":not(.hourSelected)",
                selected: eltSelection,
                start: function (event, ui) {
                    $("body").css("overflow", "hidden")
                },
                stop: selectionEnd
            });
            $("#agenda tbody tr").append($day)
        }
        daysNum = getWeek();
        $("#nav_previous").click(function () {
            daysNum = getWeek(firstDay.addDays(-1));
            fillTh($("#agenda tr th"));
            readEvents()
        });
        $("#nav_next").click(function () {
            daysNum = getWeek(lastDay.addDays(1));
            fillTh($("#agenda tr th"));
            readEvents()
        });
        $("#limitsChoice").slider({
            orientation: "vertical",
            range: true,
            min: HOUR_START,
            max: HOUR_END,
            values: [HOUR_END - params.hour_mask[MASK_END] + HOUR_START, HOUR_END - params.hour_mask[MASK_START] + HOUR_START],
            slide: limitsChange
        });
        $("#limitsChoiceHorizontal").slider({
            orientation: "horizontal",
            range: true,
            min: 0,
            max: DAYS_NUMBER,
            values: [params.day_mask[MASK_START], params.day_mask[MASK_END]],
            slide: limitsChangeHorizontal
        });
		
        readEvents();
        initCSS();
		if (conf_events) {
			createEvents();
		}
    }
})(jQuery);