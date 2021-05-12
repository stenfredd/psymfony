<template>
    <div>
        <h1>Личный кабинет</h1>

        <button v-on:click="logout()">Выйти</button>
    </div>
</template>

<script>
    import User from "../User/user.js"

    export default {
        mounted() {
            //Api.sayHi('asd')
        },
        data() {
            return { }
        },
        methods: {
            logout(e) {
                let user = new User;
                user.deleteToken();

                this.$router.push('/');
            }
        }
    }
</script>
