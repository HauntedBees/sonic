<template>
    <v-form ref="form" style="padding:10px 40px; background-color: white; width: 100%">
        <v-row>
            <v-col cols="8">
                <v-text-field
                    v-model="company.name"
                    :append-icon="'mdi-card-search'"
                    :rules="nameRules"
                    :counter="150"
                    label="Company/Organization Name"
                    required
                    @click:append="CompanySearch"
                    />
            </v-col>
            <v-col cols="4">
              <v-autocomplete
                  v-show="!showNewCategory"
                  v-model="company.type"
                  :loading="$store.state.loading"
                  :items="categories"
                  :search-input.sync="searchCategory"
                  flat
                  hide-no-data
                  hide-details
                  append-outer-icon="mdi-plus-thick"
                  @click:append-outer="showNewCategory=true"
                  label="Category/Type (Optional)"
                  />
                <v-text-field
                  v-show="showNewCategory"
                  v-model="company.newtype"
                  append-outer-icon="mdi-backspace-outline"
                  @click:append-outer="showNewCategory=false"
                  label="New Category/Type"
                  />
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="8">
                <v-autocomplete
                  v-model="company.parents"
                  :loading="$store.state.loading"
                  :items="parentItems"
                  :search-input.sync="searchParent"
                  clearable
                  cache-items
                  flat
                  hide-no-data
                  hide-details
                  multiple
                  label="Specify Parent Organizations/People"
                  />
            </v-col>
            <v-col cols="4">
                <v-combobox dense class="pt-1" v-model="company.synonyms" label="Synonyms" multiple chips/>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-autocomplete
                  v-model="company.investors"
                  :loading="$store.state.loading"
                  :items="parentItems"
                  :search-input.sync="searchInvest"
                  clearable
                  cache-items
                  flat
                  hide-no-data
                  hide-details
                  multiple
                  label="Specify Investors"
                  />
            </v-col>
            <v-col>
                <v-autocomplete
                  v-model="company.miscrelationships"
                  :loading="$store.state.loading"
                  :items="parentItems"
                  :search-input.sync="searchMisc"
                  clearable
                  cache-items
                  flat
                  hide-no-data
                  hide-details
                  multiple
                  label="Specify Misc. Relationships"
                  />
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-text-field v-model="company.description" label="Description (Optional)" :counter="1000"/>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="2">
                <v-switch
                    v-model="showIcon"
                    label="Has Logo"
                    style="margin-top:0px"
                    />
            </v-col>
            <v-col cols="2">
                <v-text-field v-show="showIcon" v-model="company.iconx" type="number" dense label="Logo X" />
            </v-col>
            <v-col cols="2">
                <v-text-field v-show="showIcon" v-model="company.icony" type="number" dense label="Logo Y" />
            </v-col>
            <v-col cols="2">
                <div v-show="showIcon"
                    :style="{'width': '32px', 'height': '32px', 'background-position': iconpos, 'background': 'url(' + require('src/assets/icons.png') + ')' }" />
            </v-col>
            <v-col cols="4">
                <SaveButton @save="Save" />
                <v-btn class="ma-2" @click="Clear">Clear</v-btn>
                <v-btn class="ma-2" to="/admin/company">Back</v-btn>
            </v-col>
        </v-row>
        <v-row>
            <AdminPageCompanyEditIssues v-if="company.id !== 0" :company-id="company.id" />
        </v-row>
    </v-form>
</template>
<script>
    import { bee, beeSecure } from "src/utils/webmethod.js";
    import AdminPageCompanyEditIssues from "src/views/Admin/AdminPageCompanyEditIssues";
    export default {
        components: { AdminPageCompanyEditIssues },
        data () {
            return {
                searchParent: null, 
                searchInvest: null, 
                searchMisc: null, 
                showNewCategory: false,
                parentItems: [], 
                initCategory: null,
                searchCategory: null,
                showIcon: null,
                categories: [],
                company: {
                    id: 0,
                    name: "",
                    description: "",
                    synonyms: [],
                    type: 0,
                    newtype: "",
                    parents: [],
                    investors: [], 
                    miscrelationships: []
                },
                nameRules: [
                    v => !!v || "Name is required",
                    v => v.length <= 150 || "Name must be under 150 characters",
                ]
            }
        },
        computed: {
            iconpos: function() {
                return `-${this.company.iconx * 32}px -${this.company.icony * 32}px`;
            }
        },
        watch: {
            searchParent(val) { val && this.QueryParent(val); },
            searchInvest(val) { val && this.QueryParent(val); },
            searchMisc(val) { val && this.QueryParent(val); },
            searchCategory(val) { val && val !== this.initCategory && this.QueryCategory(val); }
         },
        created() {
            if(this.$route.params.id.toLowerCase() === "new") { return; }
            this.company.name = this.$route.params.id;
            this.CompanySearch();
        },
        methods: {
            /*ParentFilter() { // item, query, itemText
            // TODO: make me matter? maybe?
            //console.log(item, query, itemText);
            return true;
            },*/
            QueryCategory(query) { beeSecure.get("SearchCategories", query, data => { this.categories = data.result.map(e => ({ value: e.id, text: e.name })); }); },
            QueryParent(query) { bee.get("SearchCompanies", query, data => { this.parentItems = data.result.map(e => ({ value: e.id, text: e.name })); }); },
            CompanySearch() {
                bee.get("FindCompany", this.company.name, data => {
                    this.parentItems = [];
                    for(const key in data.parentVals) {
                        const obj = data.parentVals[key];
                        obj.value = parseInt(key);
                        this.parentItems.push(obj);
                    }
                    this.categories = [{
                        value: data.result.type,
                        text: data.result.typename
                    }];
                    this.showNewCategory = false;
                    this.initCategory = this.categories[0].text;
                    this.company = data.result;
                    this.company.newtype = "";
                    this.showIcon = this.company.iconx !== null;
                });
            },
            Clear() {
                this.company = {
                    id: 0,
                    name: "",
                    description: "",
                    type: 0,
                    newtype: "",
                    parents: [], 
                    investors: [], 
                    miscrelationships: [], 
                    synonyms: []
                };
                this.$refs.form.resetValidation();
            },
            Save() {
                const res = this.$refs.form.validate();
                if(!res) { return; }
                this.company.parents = this.company.parents.map(e => parseInt(e));
                if(this.showIcon) {
                    this.company.iconx = parseInt(this.company.iconx);
                    this.company.icony = parseInt(this.company.icony);
                } else {
                    this.company.iconx = null;
                    this.company.icony = null;
                }
                beeSecure.post("SaveCompany", this.company, () => {
                    this.$store.commit("triggerMessage", "Saved successfully.");
                    this.CompanySearch();
                });
            }
        }
    }
</script>