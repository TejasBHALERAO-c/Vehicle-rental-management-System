// js/payment.js - controls the toy payment flow and animations
(function(){
  function q(id){ return document.getElementById(id); }

  // On load prefill from query string
  function qs(name){
    const params = new URLSearchParams(location.search);
    return params.get(name);
  }
  document.addEventListener('DOMContentLoaded', function(){
    const v = qs('vehicle');
    const a = qs('amount');
    const d = qs('days');
    if(v) { q('vehicle-name').textContent = decodeURIComponent(v); }
    if(a) { q('amount-display').textContent = '₹' + decodeURIComponent(a); q('amount-btn').textContent = decodeURIComponent(a); }
    if(d) { q('booking-days').textContent = decodeURIComponent(d); }
  });

  window.startPayment = function(){
    const btn = q('pay-btn');
    const wheel = q('wheel');
    const proc = q('processing');
    const msg = q('spinner-msg');

    // disable
    btn.disabled = true;
    btn.style.pointerEvents = 'none';
    msg.textContent = 'Starting payment...';

    // tiny bounce to show feedback
    btn.classList.add('btn-spinning');

    // spin the wheel with a random rotation
    const spins = Math.floor(Math.random()*6) + 6; // 6..11 full spins
    const extra = Math.floor(Math.random()*360);
    const degrees = spins * 360 + extra;
    wheel.style.transition = 'transform 2400ms cubic-bezier(.18,.9,.2,1)';
    wheel.style.transform = 'rotate('+degrees+'deg)';

    // show processing rings after wheel starts
    setTimeout(function(){
      msg.textContent = 'Processing payment...';
      proc.classList.remove('hidden');
    }, 700);

    // finalise after animation
    setTimeout(function(){
      // small success pop
      msg.textContent = 'Payment verified!';
      // generate fake txn and redirect to success page with params
      const txn = 'TXN' + Math.random().toString(36).slice(2,10).toUpperCase();
      const vehicle = encodeURIComponent(q('vehicle-name').textContent);
      const amount = encodeURIComponent(q('amount-btn').textContent);
      const days = encodeURIComponent(q('booking-days').textContent || '1');

      // add playful delay then redirect
      setTimeout(function(){
        location.href = 'payment_success.php?txn='+txn+'&vehicle='+vehicle+'&amount='+amount+'&days='+days;
      }, 900);
    }, 3200);
  };

  // Extra: allow clicking wheel to trigger payment (same behavior, keeps existing onclick)
  document.addEventListener('DOMContentLoaded', function(){
    const wheel = q('wheel');
    if(wheel){
      wheel.addEventListener('click', function(){
        const btn = q('pay-btn');
        if(!btn.disabled) startPayment();
      });
      wheel.style.cursor = 'pointer';
      wheel.setAttribute('role','button');
      wheel.setAttribute('aria-label','Spin to pay');
    }
  });

})();
