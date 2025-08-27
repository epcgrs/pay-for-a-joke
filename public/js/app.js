
$(document).ready(function() {
  const qrBase = document.querySelector('meta[name="qr-base-url"]')?.getAttribute('content') || 'https://api.qrserver.com/v1/create-qr-code/';
  const hasUmami = typeof window !== 'undefined' && typeof window.umami !== 'undefined';

  // Tracking: edição do amount
  $('.amount').on('input', function() {
    if (hasUmami) {
      window.umami.track('edit_amount', { value: $(this).val() });
    }
  });
  $('#paymentForm').on('submit', function(e) {
    e.preventDefault();

    const amount = $('.amount').val();
    const satoshis = parseInt(amount, 10);
    if (Number.isNaN(satoshis) || satoshis < 10 || satoshis > 50000) {
      alert('Por favor, insira um valor válido entre 10 e 50000 satoshis.');
      return;
    }

    if (hasUmami) {
      window.umami.track('submit_payment', { value: satoshis });
    }

    $('.loading').show();

    setTimeout(() => {
      $.post('/api/criar-invoice', { amount: satoshis })
        .done(function(response) {
          const responseData = response;
          console.log('Invoice created successfully:', responseData);

          $('.payment').show();
          const invoiceText = responseData.invoice.text;
          const encodedInvoice = encodeURIComponent(invoiceText);
          const sep = qrBase.includes('?') ? '&' : '?';
          const qrUrl = `${qrBase}${sep}data=${encodedInvoice}&size=512x512`;
          const $qrContainer = $('.qrcode');
          $qrContainer.empty();
          $('<img>').attr('src', qrUrl).appendTo($qrContainer);

          $('.lninvoice').text(invoiceText);

          $('#paymentForm').hide();
          $('.loading').hide();

          waitToPaymentConfirmation(invoiceText);
        })
        .fail(function(error) {
          console.error('Erro ao criar invoice:', error);
          alert('Ocorreu um erro ao gerar a invoice. Tente novamente mais tarde.');
          $('.loading').hide();
        });
    }, 1000);
  });

  /**
   * Verifica periodicamente o status do pagamento de uma invoice.
   * Quando o pagamento é confirmado, exibe a piada, a piada gerada por IA
   * e um link de ebook (caso exista).  Todos os dados são tratados como
   * texto para evitar execução de HTML arbitrário.
   *
   * @param {string} invoice O texto da invoice a verificar.
   */
  function waitToPaymentConfirmation(invoice) {
    function checkPayment() {
      $.get('api/checar/' + encodeURIComponent(invoice))
        .done(function(response) {
          const responseData = response;
          if (responseData.received > 0) {
            $('.payment').hide();
            $('.success').show();

            if (hasUmami) {
              window.umami.track('show_joke');
            }

            $('.piada').text(responseData.piada).show();
            $('.piadaAI').text('Gerado por IA: ' + responseData.piadaAI).show();

            if (responseData.ebook) {
              const $ebookDiv = $('.ebook');
              $ebookDiv.empty();
              $('<p>')
                .text('Obrigado pela quantia, você merece um prêmio! Resgate seu ebook de autocustódia gratuitamente:')
                .appendTo($ebookDiv);
              $('<br>').appendTo($ebookDiv);
              const $a = $('<a>')
                .attr('href', responseData.ebook.link)
                .text(responseData.ebook.title)
                .appendTo($ebookDiv);

              if (hasUmami) {
                $a.on('click', function() {
                  window.umami.track('download_ebook', { title: responseData.ebook.title });
                });
              }
              $ebookDiv.show();
            }
          } else {
            console.log('Invoice not yet received, checking again...');
            setTimeout(checkPayment, 5000);
          }
        })
        .fail(function(error) {
          console.error('Erro ao verificar status do pagamento:', error);
          setTimeout(checkPayment, 5000);
        });
    }
    checkPayment();
  }

  $('#surpriseText').on('mouseover', function() {
    $(this).css('color', '#fff');
  });
  $('#surpriseText').on('mouseout', function() {
    $(this).css('color', '#444');
  });

});