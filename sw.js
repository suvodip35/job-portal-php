// sw.js er top-level await na use kore, async function er vitore rekho
self.addEventListener('push', async function(event) {
  const data = event.data?.json() || { title: "New Job Alert", body: "Check out new jobs!" };
  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/favicon.ico'
    })
  );
});



// self.addEventListener("push", function(event) {
//   if (!event.data) return;

//   const data = event.data.json();
//   const options = {
//     body: data.body,
//     icon: "/assets/favicon.ico",
//     data: { url: data.url }
//   };

//   event.waitUntil(
//     self.registration.showNotification(data.title, options)
//   );
// });

// self.addEventListener("notificationclick", function(event) {
//   event.notification.close();
//   event.waitUntil(clients.openWindow(event.notification.data.url));
// });
