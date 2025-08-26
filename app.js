async function registerSW() {
  const registration = await navigator.serviceWorker.register("/sw.js");
  console.log("SW registered", registration);
}
registerSW();
