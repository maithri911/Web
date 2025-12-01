// script.js
$(function(){
  const $form = $('#regForm');
  const $result = $('#result');
  const $submit = $('#submitBtn');

  // show message helper
  function show(type, html){
    $result.hide().html(`<div class="${type}">${html}</div>`).slideDown(180);
  }

  // client-side basic validation
  function validate(formData){
    const errors = [];
    const name = (formData.get('name') || '').trim();
    const email = (formData.get('email') || '').trim();
    const phone = (formData.get('phone') || '').trim();
    if (!name) errors.push('Name is required.');
    if (!email) errors.push('A valid email is required.');
    if (!phone) errors.push('Phone is required.');
    return errors;
  }

  $form.on('submit', function(e){
    e.preventDefault();
    $submit.prop('disabled', true).text('Submitting...');

    const fd = new FormData(this);
    const errors = validate(fd);
    if (errors.length){
      show('error','<strong>Validation error</strong><ul><li>' + errors.join('</li><li>') + '</li></ul>');
      $submit.prop('disabled', false).text('Submit');
      return;
    }

    // AJAX POST to submit.php
    $.ajax({
      url: 'submit.php',
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'html',
      success: function(html){
        // server returns a formatted HTML block (as our submit.php does)
        show('success', html);
        $form[0].reset();
      },
     error: function(xhr){
    const msg = xhr.responseText ? xhr.responseText : 'Server error';
    show('error','<strong>Server error</strong><div>' + msg + '</div>');
},

      complete: function(){
        $submit.prop('disabled', false).text('Submit');
      }
    });
  });

  // small UX: floating label support for browsers that don't trigger :placeholder-shown
  $('input, select').on('input change blur', function(){
    // no-op; CSS handles the floating label using :not(:placeholder-shown) for inputs;
    // this keeps behaviour stable after reset
  });
});