<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">Восстановление пароля</div>

                    <div class="card-body">
                        <form @submit="submitReset">
                            <div>
                                <div class="top_error" v-if="invalidEmail">Указанный email не зарегистрирован</div>

                                <div class="form-group" :class="{ 'form-group--error': $v.email.$error }">
                                    <label class="form__label">Email</label>
                                    <input class="form-control" v-model.trim="$v.email.$model"/>

                                    <div v-if="$v.email.$dirty">
                                        <div class="error" v-if="!$v.email.required">Укажите ваш email</div>
                                        <div class="error" v-if="!$v.email.email">Укажите корректный email</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button class="btn btn-primary" type="submit" :disabled="sendProcess">Восстановить пароль</button>
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
    import './styles/auth.css'
    import Api from '../../components/system/api.js';

    import { required, minLength, between } from 'vuelidate/lib/validators'
    import email from "vuelidate/lib/validators/email";
    import maxLength from "vuelidate/lib/validators/maxLength";
    import User from "./user.js"

    export default {
        mounted() {
            //Api.sayHi('asd')
        },
        data() {
            return {
                sendProcess: false,
                email: '',
                invalidEmail: false
            }
        },
        validations: {
            email: {
                required,
                email
            }
        },
        methods: {
            submitReset(e) {
                e.preventDefault();

                this.$v.$touch();

                if (this.$v.$invalid) {
                    return;
                }

                this.sendProcess = true;
                this.invalidEmail = false;
                let api = new Api;
                api.post('/auth/email/reset-password', {email: this.email}, function (data) {
                    console.log(data);

                    this.$swal.fire('Готово!', 'На указанный вами email была отправлена инструкция по восстановлению пароля', 'success').then((result) => {
                        this.$router.push('/user/cabinet');
                    });
                }.bind(this), function (data) {
                    this.sendProcess = false;

                    if (data.error.message === 'User not found') {
                        this.invalidEmail = true;
                    }
                }.bind(this));

            }
        }
    }


</script>
