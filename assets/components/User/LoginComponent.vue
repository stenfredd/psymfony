<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">Авторизация</div>

                    <div class="card-body">
                        <form @submit="submitLogin">
                            <div>
                                <div class="top_error" v-if="invalidLogin">Неправильное имя пользователя или пароль</div>

                                <div class="form-group" :class="{ 'form-group--error': $v.email.$error }">
                                    <label class="form__label">Email</label>
                                    <input class="form-control" v-model.trim="$v.email.$model"/>

                                    <div v-if="$v.email.$dirty">
                                        <div class="error" v-if="!$v.email.required">Укажите ваш email</div>
                                        <div class="error" v-if="!$v.email.customemail">Укажите корректный email</div>
                                    </div>
                                </div>

                                <div class="form-group" :class="{ 'form-group--error': $v.password.$error }">
                                    <label class="form__label">Password</label>
                                    <input class="form-control" type="password" v-model.trim="$v.password.$model"/>

                                    <div v-if="$v.password.$dirty">
                                        <div class="error" v-if="!$v.password.required">Укажите ваш пароль</div>
                                        <div class="error" v-if="!$v.password.minLength || !$v.password.maxLength">Пароль должен быть от {{$v.password.$params.minLength.min}} до {{$v.password.$params.maxLength.max}}</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button class="btn btn-primary" type="submit" :disabled="sendProcess">Войти</button>
                                </div>

                                <router-link to="/sign-up">Зарегистрироваться</router-link><br>
                                <router-link to="/reset-password">Забыли пароль?</router-link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import './styles/auth.css'
    import Api from '../../components/system/api.js';

    import {required, minLength, between} from 'vuelidate/lib/validators'
    import email from "vuelidate/lib/validators/email";
    import maxLength from "vuelidate/lib/validators/maxLength";
    import User from "./user.js"
    import { helpers } from 'vuelidate/lib/validators'

    const customemail = helpers.regex("customemail", /^[a-zA-Z0-9\-\_]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/);

    export default {
        mounted() {
            //Api.sayHi('asd')
        },
        data() {
            return {
                email: '',
                password: '',
                sendProcess: false,
                invalidLogin: false
            }
        },
        validations: {
            email: {
                required,
                customemail
            },
            password: {
                required,
                minLength: minLength(6),
                maxLength: maxLength(128)
            }
        },
        methods: {
            submitLogin(e) {
                e.preventDefault();

                this.$v.$touch();

                if (this.$v.$invalid) {
                    return;
                }

                this.sendProcess = true;
                let api = new Api;
                api.post('/auth/email/login', {email: this.email, password: this.password}, function (data) {
                    console.log(data);
                    this.sendProcess = false;

                    let user = new User;
                    user.setAuthToken(data.data.token);

                    api.get('/user/personal-data', function (data) {
                        user.setUserData(data.data);

                        if (user.hasPermission('ADMIN_PANEL')) {
                            this.$router.push('/admin/desktop');
                        }
                        if (user.hasPermission('PERSONAL_CABINET')) {
                            this.$router.push('/user/cabinet');
                        }

                    }.bind(this), function (data) {
                        alert('Непредвиденная ошибка 198_1');
                    }.bind(this));

                }.bind(this), function (data) {
                    this.sendProcess = false;
                    if (data.error.message === 'Invalid username or password') {
                        this.invalidLogin = true;
                    }
                }.bind(this));

            }
        }
    }


</script>
