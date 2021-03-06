

$(function(){
  var searchWordElem = $("#searchWord");
  var searchCountElem = $("#searchCount");
  var searchResultElem = $("#searchResult");
  var apiUrl = "http://" + document.domain + ":8010/";

  var displayTable = function(data) {
    var tableElem = $("<table/>");
    $.each(data, function(colName,row){
      var trElem = $("<tr/>");
      $.each(row, function(index,value){
        var tdElem = $("<td/>");
        tdElem.text(value);
        trElem.append(tdElem);
      });
      tableElem.append(trElem);
    });
    searchResultElem.text("");
    searchResultElem.append(tableElem);
  };

  var executeCnt = 0;
  var executeSearch = function(word) {
    var startTime = new Date();
    var searchWordList = word.split(/\s+/);
    var data = "";
    $.each(searchWordList, function(index, value) {
      if (value.length < 2) {
        return true;
      }
      if (data != "") {
        data += "&";
      }
      data += "s" + encodeURI("[]") + "=" + encodeURI(value);
    });
    $.ajax({
      type: "GET",
      url: apiUrl,
      data: data,
      success: function(json) {
        var endTime = new Date();
        var executeTime = ((endTime - startTime) / 1000).toFixed(3);
        searchCountElem.text((new Intl.NumberFormat).format(json.count) + " 件 (" + executeTime + "秒)");
        displayTable(json.data);
      }
    });
  };

  var timeoutId;
  searchWordElem.autocomplete({
    delay: 0,
    source: function(request, response) {
      if (timeoutId) {
        clearTimeout(timeoutId);
        delete timeoutId;
      }
      timeoutId = setTimeout(function() {
        executeSearch(request.term);
      }, 500);
    }
  });

  searchWordElem.focus();
  executeSearch(searchWordElem.val());
});
