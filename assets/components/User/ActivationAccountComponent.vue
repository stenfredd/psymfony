<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">Аккаунт не активирован</div>

                    <div class="card-body">
                        <div>
                            <p>Для активации аккаунта перейдите по ссылке, которая была отправлена на ваш email при регистрации.</p>
                            <p></p>
                            <p><button class="btn btn-primary" type="button" v-on:click="resendActivationLink()">Отправить ссылку повторно</button></p>
                            <button class="btn" style="float: right" type="button" v-on:click="logout()">Выйти</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import './styles/auth.css'
    import Api from '../../components/system/api.js';

    import { required, minLength, between } from 'vuelidate/lib/validators'
    import email from "vuelidate/lib/validators/email";
    import maxLength from "vuelidate/lib/validators/maxLength";
    import User from "./user.js"

    export default {
        mounted() {
            this.checkActivation();
        },
        data() {
            return {
                email: '',
                password: '',
                invalidLogin: false
            }
        },
        methods: {
            resendActivationLink() {
                let api = new Api;

                api.post('/auth/email/resend-activation-link', {}, function (data) {
                    this.$swal.fire('Готово!', 'Ссылка для активации аккаунта повторно отправлена на ваш email', 'success').then((result) => {

                    });
                }.bind(this), function (data) {
                    alert('Непредвиденная ошибка 197_1');
                }.bind(this));
            },
            checkActivation() {
                let api = new Api;
                api.get('/user/personal-data', function (data) {
                    let user = new User;
                    user.setUserData(data.data);

                    if (user.isActive()) {
                        if (user.hasPermission('ADMIN_PANEL')) {
                            this.$router.push('/admin/desktop');
                        }
                        if (user.hasPermission('PERSONAL_CABINET')) {
                            this.$router.push('/user/cabinet');
                        }
                    }

                }.bind(this), function (data) {
                    alert('Непредвиденная ошибка 198_1');
                }.bind(this));
            },
            logout(e) {
                let user = new User;
                user.deleteToken();

                this.$router.push('/');
            }
        }
    }


</script>
