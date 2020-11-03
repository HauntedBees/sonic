<template>
    <v-container>
        <form class="login" @submit.prevent="login">
            <v-row>
                <v-col cols="4"/>
                <v-col cols="4">
                    <h1>Log dat ass in</h1>
                    <v-text-field solo v-model="username" label="Username" required />
                    <v-text-field
                        v-model="password"
                        :append-icon="showPassword?'mdi-eye':'mdi-eye-off'"
                        :type="showPassword?'text':'password'"
                        label="Password"
                        solo
                        required
                        @click:append="showPassword=!showPassword"
                        />
                    <SaveButton @save="Login" text="Log In" />
                </v-col>
                <v-col cols="4"/>
            </v-row>
        </form>
    </v-container>
</template>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        data() {
            return {
                showPassword: false,
                username: "",
                password: ""
            };
        },
        methods: {
            Login: function() {
                this.$store.commit("logout");
                bee.post("Login", {
                    username: this.username,
                    password: this.password
                }, res => {
                    console.log(res.result);
                    this.$store.commit("login", [this.username, res.result]);
                    this.$router.push("/admin");
                }, true);
            }
        }
    }
</script>