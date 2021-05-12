<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">Регистрация</div>

                    <div class="card-body">
                        <form @submit="submitSignup">
                            <div>
                                <div class="top_error" v-if="invalidEmail">Указанный вами email уже зарегистрирован</div>

                                <div class="form-group" :class="{ 'form-group--error': $v.email.$error }">
                                    <label class="form__label">Email</label>
                                    <input class="form-control" v-model.trim="$v.email.$model"/>

                                    <div v-if="$v.email.$dirty">
                                        <div class="error" v-if="!$v.email.required">Укажите ваш email</div>
                                        <div class="error" v-if="!$v.email.email">Укажите корректный email</div>
                                    </div>
                                </div>

                                <div class="form-group" :class="{ 'form-group--error': $v.nickname.$error }">
                                    <label class="form__label">Имя</label>
                                    <input class="form-control" v-model.trim="$v.nickname.$model"/>

                                    <div v-if="$v.email.$dirty">
                                        <div class="error" v-if="!$v.nickname.required">Укажите ваше имя</div>
                                        <div class="error" v-if="!$v.nickname.minLength || !$v.nickname.maxLength">Имя должно быть от {{$v.nickname.$params.minLength.min}} до {{$v.nickname.$params.maxLength.max}}</div>
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
                                    <input type="checkbox" id="accept" value="Джек" v-model="$v.accept.$model">
                                    <label for="accept">Я согласен с условиями оферты</label>

                                    <div v-if="$v.accept.$dirty">
                                        <div class="error" v-if="!$v.accept.required">Необходимо дать согласие</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button class="btn btn-primary" type="submit" :disabled="sendProcess">Регистрация</button>
                                </div>

                                <router-link to="/login">Войти</router-link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import 'sweetalert2/dist/sweetalert2.min.css';

    import './styles/auth.css'
    import Api from '../../components/system/api.js';

    import { required, minLength, between } from 'vuelidate/lib/validators'
    import email from "vuelidate/lib/validators/email";
    import maxLength from "vuelidate/lib/validators/maxLength";



    export default {
        mounted() {
            //Api.sayHi('asd')
        },
        data() {
            return {
                sendProcess: false,
                email: '',
                password: '',
                nickname: '',
                accept: '',
                invalidEmail: false
            }
        },
        validations: {
            email: {
                required,
                email
            },
            password: {
                required,
                minLength: minLength(6),
                maxLength: maxLength(128)
            },
            nickname:{
                required,
                minLength: minLength(5),
                maxLength: maxLength(32)
            },
            accept:{
                required
            }
        },
        methods: {
            submitSignup(e) {
                e.preventDefault();

                this.$v.$touch();

                if (this.$v.$invalid) {
                    return;
                }

                this.invalidEmail = false;
                this.sendProcess = true;
                let api = new Api;
                api.post('/auth/email/sign-up', {email: this.email, password: this.password, nickname: this.nickname}, function (data) {
                    console.log(data);

                    this.$swal.fire('Регистрация прошла успешно!', 'На указанный вами email была отправлена инструкция по активации аккаунта', 'success').then((result) => {
                        this.$router.push('/login');
                    });
                }.bind(this), function (data) {
                    console.log(data);

                    this.sendProcess = false;

                    if (data.error.message === 'User with this email is already registered') {
                        this.invalidEmail = true;
                    }
                }.bind(this));

            }
        }
    }


</script>