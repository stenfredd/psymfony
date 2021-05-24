import VueRouter from 'vue-router';
import Vue from 'vue';
import User from "./components/User/user.js"

Vue.use(VueRouter);

import MainpageComponent from "./components/MainpageComponent";

// User
import LoginComponent from "./components/User/LoginComponent";
import AdminDesktopComponent from "./components/AdminDesktop/AdminDesktopComponent";
import PersonalCabinetComponent from "./components/PersonalCabinet/PersonalCabinetComponent";
import SignupComponent from "./components/User/SignupComponent";
import AccountActivatedComponent from "./components/User/AccountActivatedComponent";
import AccountActivationFailedComponent from "./components/User/AccountActivationFailedComponent";
import ResetPasswordComponent from "./components/User/ResetPasswordComponent";
import PasswordChangedComponent from "./components/User/PasswordChangedComponent";
import PasswordChangeFailedComponent from "./components/User/PasswordChangeFailedComponent";
import ActivationAccountComponent from "./components/User/ActivationAccountComponent";

const routes = [
    { path: '/', component: MainpageComponent },
    { path: '/login', component: LoginComponent, meta: { guest: true }},
    { path: '/activate', component: ActivationAccountComponent, meta: { auth: true }},
    { path: '/sign-up', component: SignupComponent, meta: { guest: true }},
    { path: '/user/cabinet', component: PersonalCabinetComponent, meta: { auth: true, active: true }},
    { path: '/account-activated', component: AccountActivatedComponent},
    { path: '/account-activation-filed', component: AccountActivationFailedComponent},
    { path: '/reset-password', component: ResetPasswordComponent},
    { path: '/password-changed', component: PasswordChangedComponent},
    { path: '/password-change-filed', component: PasswordChangeFailedComponent},
];

let router = new VueRouter({
    mode: "history",
    routes
});

export default router;

router.beforeEach((to, from, next) => {
    console.log(to);

    if (to['meta']['guest']) {
        let user = new User();
        if (user.hasAuthToken()) {
            if (user.hasPermission('ADMIN_PANEL')) {
                next('/admin/desktop');
            }
            if (user.hasPermission('PERSONAL_CABINET')) {
                next('/user/cabinet');
            }
        }
    }

    if (to['meta']['auth']) {
        let user = new User();
        if (!user.hasAuthToken()) {
            return next('/login');
        }

        if (to['meta']['active']) {
            if (!user.isActive()) {
                return next('/activate');
            }
        }
    }

    return next();
})