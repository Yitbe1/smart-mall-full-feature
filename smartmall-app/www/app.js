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