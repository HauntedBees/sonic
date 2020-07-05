<template>
    <v-btn class="ma-2" @click="Save" :dark="dark" :disabled="$store.state.loading" :color="dispcolor" :text="modal">
        {{text || "Save"}}
        <v-progress-circular v-if="$store.state.loading" style="margin-left:10px" size="16" width="2" indeterminate />
    </v-btn>
</template>
<script>
    export default {
        props: {
            "color": { type: String },
            "text": { type: String },
            "dark": { type: Boolean },
            "modal": { type: Boolean }
        },
        computed: {
            dispcolor() {
                if(this.color) { return this.color; }
                return this.modal ? "blue darken-1" : "primary";
            }
        },
        methods: {
            Save() { this.$emit("save"); }
        }
    }
</script>