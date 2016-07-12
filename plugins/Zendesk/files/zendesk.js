var zendeskArticles = null;
var zendeskArticlesVisible = false;
var zendeskTypingTimer;
var zendeskDoneTypingInterval = 500;

function showSearchResults(show) {
  var articles = $('div#zendesk #articles');
  var noteDiv = $('div#note-div');

  if (show) {
    articles.show();
    noteDiv.removeClass('col-md-12');
    noteDiv.addClass('col-md-8');
  } else {
    articles.hide();
    noteDiv.removeClass('col-md-8');
    noteDiv.addClass('col-md-12');
  }
}

function fetchData(callback) {
  var queryText = $("div#note-div input#query").val();
  queryText = queryText.trim();

  if (queryText.length >= 3) {
    var url = "xmlhttprequest.php?entrypoint=plugin_zendesk_articles&query=" + encodeURI(queryText);
    var json = $.ajax({ "url": url }).done(function() {
      var data = JSON.parse(json.responseText);
      zendeskArticles = data.results;
      callback(zendeskArticles);
    });
  } else {
    callback([]);
  }
}

function populateListBox(values) {
  var articles = $('div#zendesk #articles');
  articles.html('');

  $.each(values, function(i, o) {
    $('<option value="' + o.html_url + '">' + o.title + '</option>').appendTo(articles);
  });

  if (values.length > 0) {
    showSearchResults(true);
  } else {
    showSearchResults(false);
  }
}

function zendeskDoneTyping() {
  fetchData(function(data) {
    var filteredArticles = [];
    var query = $("div#note-div input#query");
    var results = data.forEach(function(e) {
      var search = query.val().toLowerCase();
      if (e.title.toLowerCase().indexOf(search) != -1 ||
          e.body.toLowerCase().indexOf(search) != -1) {
        filteredArticles.push(e);
      }
    });

    populateListBox(filteredArticles);
  });
}

function showZendeskControl() {
  var note = $("textarea[name='bugnote_text']");
  if (note.length == 0) {
    return;
  }

  var noteParent = note.parent();

  var noteDiv = $('<div id="note-div" class="col-md-12 col-xs-12"></div>');
  noteDiv.appendTo(noteParent);
  note.detach();
  note.appendTo(noteDiv);

  var zendeskDiv = $('<div id="zendesk" class="col-md-4 col-xs-12" id="zendesk"></div>');
  zendeskDiv.appendTo(noteParent);

  var query = $('<input type="input" id="query" size="20" placeholder="Search Zendesk" autocomplete="off" />');
  query.css('margin-bottom', '10px');
  query.css('float', 'right');
  query.insertBefore(note);

  query.keypress(function(e) {
    if (e.which == 13) {
      e.preventDefault();
      return false;
    }
  });

  query.on('keyup', function () {
      clearTimeout(zendeskTypingTimer);
      zendeskTypingTimer = setTimeout(zendeskDoneTyping, zendeskDoneTypingInterval);
  });

  query.on('keydown', function () {
      clearTimeout(zendeskTypingTimer);
  });

  var articles = $('<select size="7" id="articles"></select>');
  articles.css('display', 'none');
  articles.appendTo(zendeskDiv);

  articles.dblclick(function() {
    var item = $(this).find(':selected');

    var text = '- ' + item.text() + "\n" + item.attr('value') + "\n";

    var note = $("textarea[name='bugnote_text']");
    note.textrange('replace', text);
    var range = note.textrange();
    note.textrange('set', range.end, 0);
  });

}

$(function() {
  showZendeskControl();
});
