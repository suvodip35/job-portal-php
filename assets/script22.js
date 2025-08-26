// assets/script.js
document.addEventListener('DOMContentLoaded', function() {
  const themeToggle = document.getElementById('themeToggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      document.documentElement.classList.toggle('dark');
      try {
        if (document.documentElement.classList.contains('dark')) {
          localStorage.setItem('theme', 'dark');
        } else {
          localStorage.setItem('theme', 'light');
        }
      } catch (e) {}
    });
  }

  // const subscribeBtn = document.getElementById('subscribePushBtn');
  // if (subscribeBtn && 'serviceWorker' in navigator && 'PushManager' in window) {
  //   subscribeBtn.addEventListener('click', async () => {
  //       try {
  //           const reg = await navigator.serviceWorker.register('/sw.js');
  //           console.log('SW registered', reg);
  //           // Ask for permission
  //           const permission = await Notification.requestPermission();
  //           if (permission !== 'granted') {
  //               alert('Please enable notifications in your browser settings.');
  //               return;
  //           }
  //           // subscribe to push (this endpoint expects server/public VAPID key)
  //           const response = await fetch('/subscribe_push.php', {
  //               method: 'POST',
  //               headers: {'Content-Type': 'application/json'},
  //               body: JSON.stringify({action: "subscribe"})
  //           });
  //           const data = await response.json();
  //           if (data.success) {
  //               alert('Subscribed for job alerts!');
  //           } else {
  //               alert('Subscription failed: ' + (data.message || 'Unknown'));
  //           }
  //       } catch (err) {
  //           console.error(err);
  //           alert('Push subscription error: ' + err.message);
  //       }
  //   });
  // }
});
