<template>
    <v-container>
        <v-row class="text-center">
            <v-col cols="12">
                <v-img
                  :src="require('../assets/logo.png')"
                  class="my-3"
                  contain dark
                  height="608">
                  <CompanyAutocomplete 
                    addtl-style="width: 260px; margin: 269px auto !important; margin-bottom: 2px !important"
                    @select="Select" />
                  <router-link class="homelink" to="/browse/companies">{{$t("browse")}}</router-link> |
                  <router-link class="homelink" to="/info/about">{{$t("whatIsThis")}}</router-link>
                </v-img>
            </v-col>
        </v-row>
    </v-container>
</template>
<script>
    export default {
        methods: {
            Select(val) { this.$router.push(val); }
        }
    }
</script>