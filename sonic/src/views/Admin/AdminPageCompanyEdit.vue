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
            <v-col cols="1"/>
            <v-col cols="2">
                <v-switch
                    v-model="showIcon"
                    label="Has Logo"
                    style="margin-top:0px"
                    />
            </v-col>
            <v-col cols="2">
                <v-text-field v-show="showIcon" v-model="company.img" type="number" dense label="File #" />
            </v-col>
            <v-col cols="2">
                <v-text-field v-show="showIcon" v-model="company.iconx" type="number" dense label="Logo X" />
            </v-col>
            <v-col cols="2">
                <v-text-field v-show="showIcon" v-model="company.icony" type="number" dense label="Logo Y" />
            </v-col>
            <v-col cols="2">
                <div v-if="showIcon && parseInt(company.img) === 0"
                    :style="{'width': '32px', 'height': '32px', 'background-position': iconpos, 'background': 'url(' + require('src/assets/icons.png') + ')' }" />
                <div v-if="showIcon && parseInt(company.img) === 1"
                    :style="{'width': '32px', 'height': '32px', 'background-position': iconpos, 'background': 'url(' + require(`src/assets/icons2.png`) + ')' }" />
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="5" />
            <v-col cols="7">
                <SaveButton @save="Save" />
                <v-btn class="ma-2" @click="Clear">Clear</v-btn>
                <v-btn class="ma-2" @click="ClearPartial">Partial Clear</v-btn>
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
                    img: 0,
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
            QueryCategory(query) { beeSecure.get("Categories", [query], data => { this.categories = data.result.map(e => ({ value: e.id, text: e.name })); }); },
            QueryParent(query) { bee.get("CompanySearch", [query], data => { this.parentItems = data.result.map(e => ({ value: e.id, text: e.name })); }); },
            CompanySearch() {
                bee.get("Company", [this.company.name], data => {
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
                    this.company.investors = [];
                    this.company.miscrelationships = [];
                    this.showIcon = this.company.iconx !== null;
                    beeSecure.get("AdditionalCompanyInfo", [this.company.id], addtldata => {
                        addtldata.investors.forEach(e => {
                            this.parentItems.push({
                                value: e.id,
                                text: e.name
                            });
                        });
                        addtldata.relationships.forEach(e => {
                            this.parentItems.push({
                                value: e.id,
                                text: e.name
                            });
                        });
                        this.company.investors = addtldata.investors.map(t => t.id);
                        this.company.miscrelationships = addtldata.relationships.map(t => t.id);
                    });
                    
                });
            },
            Clear() {
                this.company = {
                    id: 0,
                    img: 0,
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
            ClearPartial() {
                const currentIcon = this.company.img;
                const currentIconX = this.company.iconx;
                const currentIconY = this.company.icony;
                const currentCategory = this.company.type;
                const currentParents = this.company.parents;
                this.company = {
                    id: 0,
                    img: currentIcon,
                    iconx: currentIconX,
                    icony: currentIconY,
                    name: "",
                    description: "",
                    type: currentCategory,
                    newtype: "",
                    parents: currentParents, 
                    investors: [], 
                    miscrelationships: [], 
                    synonyms: []
                };
                this.$refs.form.resetValidation();
            },
            Save() {
                const res = this.$refs.form.validate();
                if(!res) { return; }
                this.company.name = this.company.name.trim();
                this.company.parents = this.company.parents.map(e => parseInt(e));
                if(this.showIcon) {
                    this.company.iconx = parseInt(this.company.iconx);
                    this.company.icony = parseInt(this.company.icony);
                    this.company.img = parseInt(this.company.img);
                } else {
                    this.company.iconx = null;
                    this.company.icony = null;
                    this.company.img = 0;
                }
                beeSecure.post("Company", this.company, () => {
                    this.$store.commit("triggerMessage", "Saved successfully.");
                    this.CompanySearch();
                });
            }
        }
    }
</script>