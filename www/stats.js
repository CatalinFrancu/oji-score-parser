$(function() {
  $('#rankings tbody tr').click(rowClick);
  $('#statsHide').click(function() {
    $('#stats').hide();
    return false;
  });
  $(document).on('keydown', function (e) {
    if (e.keyCode === 27) { // Esc
      $('#stats').hide();
    }
  });
  $('header select').change(function(){
    window.location = $(this).val();
  });

  var r = $('#rankings');
  $('#stats').css('left', r.position().left + r.width());
});

function rowClick() {
  // Collect counties in a name -> frequency dictionary
  var dict = {};
  var rows = $(this).prevAll().andSelf();
  rows.each(function() {
    var c = $(this).data('county');
    if (!dict[c]) {
      dict[c] = 1;
    } else {
      dict[c]++;
    }
  });

  // Convert the dictionary to an array of pairs and sort the pairs
  var pairs = [];
  for (var key in dict) {
    pairs.push([key, dict[key]]);
  }

  pairs.sort(function(a, b) {
    return b[1] - a[1];
  });

  // Empty the existing table, except for the stem
  $('#distribution tbody tr').not('[class="stem"]').remove();

  // Clone the row and populate it for every county
  var n = rows.length;
  $('#sampleSize').text(n);
  for (var i = 0; i < pairs.length; i++) {
    var clone = $('tr.stem').clone().removeClass('stem');
    var pc = (100 * pairs[i][1] / n).toFixed(2);
    clone.find('.county').text(pairs[i][0]);
    clone.find('.frequency').text(pairs[i][1]);
    clone.find('.percentage').text(pc).css('border-width', 1 + 3 * pc);
    $('#distribution tbody').append(clone);
  }

  $('#stats').show();
}
