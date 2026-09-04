// AmpDuel-style UI behavior and AJAX posting wrapper
// Assumes jQuery and SweetAlert2 are available

function showPostingOverlay(message){
  if (!message) message = 'Posting...';
  if ($('#postingOverlay').length === 0) {
    $('body').append('<div id="postingOverlay"><div class="box"><div class="spinner"></div><div style="margin-top:12px;color:#cfe8ff;font-weight:600;">'+message+'</div></div></div>');
  } else {
    $('#postingOverlay .box div:last-child').text(message);
  }
  $('#postingOverlay').fadeIn(120);
  $('.selectionbtn, #okbtn, .item-card, .btn-primary').prop('disabled', true).addClass('disabled');
}
function hidePostingOverlay(){
  $('#postingOverlay').fadeOut(120);
  $('.selectionbtn, #okbtn, .item-card, .btn-primary').prop('disabled', false).removeClass('disabled');
}

// Standardized AJAX post for create
function createMatchPost(side, items){
  showPostingOverlay('Posting game...');
  $.ajax({
    url: './php/game.php',
    type: 'POST',
    data: { type: 'create', side: side, item_ids: JSON.stringify(items) },
    success: function(res){
      hidePostingOverlay();
      try { if (typeof res === 'string') res = JSON.parse(res); } catch(e){}
      if (!res) { Swal.fire('Error','Empty response from server','error'); return; }
      if (res.Error) { Swal.fire('Error', res.Error, 'error'); return; }
      Swal.fire({ title: 'Match Created', text: 'Your match was posted.', icon: 'success' }).then(function(){ window.location.reload(); });
    },
    error: function(xhr){ hidePostingOverlay(); Swal.fire('Network Error','Failed to post game, try again','error'); }
  });
}

// Standardized AJAX post for play/join
function joinMatchPost(game_id, items){
  showPostingOverlay('Joining match...');
  $.ajax({
    url: './php/game.php',
    type: 'POST',
    data: { type: 'play', game_id: game_id, item_ids: JSON.stringify(items) },
    success: function(res){
      hidePostingOverlay();
      try { if (typeof res === 'string') res = JSON.parse(res); } catch(e){}
      if (!res) { Swal.fire('Error','Empty response from server','error'); return; }
      if (res.Error) { Swal.fire('Error', res.Error, 'error'); return; }
      // on success, either wait for socket game played event or show result
      Swal.fire({ title: 'Joined', text: 'You joined the match.', icon: 'success' }).then(function(){ window.location.reload(); });
    },
    error: function(){ hidePostingOverlay(); Swal.fire('Network Error','Failed to join game, try again','error'); }
  });
}

// enhance existing popup item card behavior: toggle selected class
$(document).on('click', '.item-card', function(){
  $(this).toggleClass('selected');
  var inv = $(this).data('inv');
  var val = parseInt($(this).data('val')) || 0;
  if (!window.__xflips_items) window.__xflips_items = { items: [], value: 0 };
  var idx = window.__xflips_items.items.indexOf(inv);
  if (idx === -1) { window.__xflips_items.items.push(inv); window.__xflips_items.value += val; $(this).find('.selectionbtn').text('Unselect').removeClass('btn-secondary').addClass('btn-primary'); }
  else { window.__xflips_items.items.splice(idx,1); window.__xflips_items.value -= val; $(this).find('.selectionbtn').text('Select').removeClass('btn-primary').addClass('btn-secondary'); }
  $('#valdiv').text('Value: ' + window.__xflips_items.value);
  // enable/disable okbtn
  var ok = (window.__xflips_items.items.length > 0);
  $('#okbtn').prop('disabled', !ok);
});

// side selection
$(document).on('click', '#side0,#side1', function(){
  var id = $(this).attr('id');
  $('#side0,#side1').css('outline','');
  $(this).css('outline','3px solid rgba(158,207,255,0.6)');
  window.__xflips_side = id === 'side0' ? 0 : 1;
});

// wire the okbtn for posting depending on mode
$(document).on('click', '#okbtn', function(){
  var mode = $(this).data('mode') || 'create';
  var items = window.__xflips_items ? window.__xflips_items.items : [];
  if (mode === 'create') {
    if (typeof window.__xflips_side === 'undefined') { Swal.fire('Error','Please select a side','error'); return; }
    createMatchPost(window.__xflips_side, items);
  } else if (mode === 'join') {
    var gid = $(this).data('gameid');
    if (!gid) { Swal.fire('Error','Missing game id','error'); return; }
    joinMatchPost(gid, items);
  }
});
