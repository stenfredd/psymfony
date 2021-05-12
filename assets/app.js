import './styles/app.css';
import './styles/bootstrap.min.css';
import 'sweetalert2/dist/sweetalert2.min.css';

import Vue from 'vue';
import Vuelidate from 'vuelidate';
import VueSweetalert2 from 'vue-sweetalert2';

Vue.use(Vuelidate);
Vue.use(VueSweetalert2);

import router from "./router";

import App from './components/App';

new Vue({
    components: { App },
    template: "<App/>",
    router
}).$mount("#app");
