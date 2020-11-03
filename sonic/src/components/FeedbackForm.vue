<template>
    <v-container>
        <v-row v-if="path" >
            <v-col>
                <v-text-field readonly v-model="path" filled />
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-textarea :counter="1000" v-model="form.text" filled required label="Tell me your thoughts" />
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-text-field :counter="69" v-model="form.name" filled label="Name (optional)" />
            </v-col>
            <v-col>
                <v-text-field :counter="69" v-model="form.contact" filled label="Email/Contact Info (optional)" />
            </v-col>
        </v-row>
        <v-row>
            <v-col style="text-align: center">
                <img style="margin-top: 10px" v-if="imgUrl!==''" :src="imgUrl" />
            </v-col>
            <v-col>
                <v-text-field v-model="form.captcha" filled label="CAPTCHA" />
            </v-col>
        </v-row>
        <v-row>
            <v-col style="text-align: center">
                <SaveButton @save="SubmitFeedback" dark text="Submit" color="#F90018" />
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <p>
                    Sorry for the CAPTCHA - we know they're not the most accessible, but we're not aware of any better ways to 
                    make sure you aren't a robot or troll spamming us with tons of garbage (other than using some fancier third-party 
                    verification system, but we're skeptical of privacy issues surrounding them). If you can't use a CAPTCHA, you can message us on Twitter at
                    <a rel="external nofollow noopener noreferrer" href="https://twitter.com/hauntedbees" target="_blank">@hauntedbees</a> or via email at 
                    <a rel="external nofollow noopener noreferrer" href="mailto:fench@hauntedbees.com">fench@hauntedbees.com</a>.
                </p>
            </v-col>
        </v-row>
    </v-container>
</template>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        props: {
            "page": { type: String },
            "issueID": { type: Number, default: -1 },
            "modal": { type: Boolean }
        },
        data: () => ({
            form: {
                text: "",
                name: "",
                contact: "",
                captcha: "",
                token: "",
                issue: -1
            },
            imgUrl: ""
        }),
        computed: {
            path() {
                if(!this.page) { return ""; }
                if(this.page.split("/").length > 2) { return ""; }
                return this.page.substring(1);
            }
        },
        created() { this.GetCaptcha(); },
        methods: {
            GetCaptcha() {
                bee.get("Captcha", "", data => {
                    this.imgUrl = data.img;
                    this.form.token = data.token;
                });
            },
            SubmitFeedback() {
                if(this.form.name.length >= 70) { return this.$store.commit("triggerError", "No more than 70 characters for your name, please."); }
                if(this.form.contact.length >= 70) { return this.$store.commit("triggerError", "No more than 70 characters for your contact info, please."); }
                if(this.form.text.length > 1000) { return this.$store.commit("triggerError", "No more than 1000 characters for your feedback, please."); }
                const path = this.path;
                this.form.path = path;
                this.form.issue = this.issueID;
                bee.post("Feedback", this.form, () => {
                    this.form = {
                        text: "",
                        name: "",
                        captcha: "",
                        contact: "",
                        issue: -1
                    };
                    this.$store.commit("triggerMessage", "Thank you for your feedback!");
                    if(this.modal) {
                        this.$emit("close");
                    }
                }, true);
            }
        }
    }
</script>