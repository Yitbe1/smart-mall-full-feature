import { App } from '@capacitor/app';
import { StatusBar } from '@capacitor/status-bar';
import { SplashScreen } from '@capacitor/splash-screen';

StatusBar.hide();
SplashScreen.hide();

App.addListener('backButton', ({ canGoBack }) => {
    canGoBack ? window.history.back() : App.exitApp();
});

App.addListener('appUrlOpen', ({ url }) => {
    if (url.includes('smartmall.unaux.com/')) window.location.href = url;
});
import { PushNotifications } from '@capacitor/push-notifications';

const addListeners = async () => {
  await PushNotifications.addListener('registration', token => {
    console.info('Registration token: ', token.value);
  });

  await PushNotifications.addListener('pushNotificationReceived', notification => {
    console.info('Push received: ', notification);
  });
};

const registerNotifications = async () => {
  let permStatus = await PushNotifications.checkPermissions();

  if (permStatus.receive === 'prompt') {
    permStatus = await PushNotifications.requestPermissions();
  }

  if (permStatus.receive !== 'granted') {
    throw new Error('User denied permissions!');
  }

  await PushNotifications.register();
};
