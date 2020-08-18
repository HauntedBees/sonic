export default {
    en: { 
        whatIsThis: "what is this?",                        // views/LandingPage.vue
        browse: "browse",                                   // views/LandingPage.vue
        issues: "Issues",                                   // components/BeeCompany.vue
        subsidiaries: "Subsidiaries",                       // components/BeeCompany.vue
        cw: "Content Warning",                              // components/BeeIssue.vue
        showDetails: "Click here to show the details.",     // components/BeeIssue.vue
        viewSource: "View Source",                          // components/BeeIssue.vue
        reportInaccuracy: "Report Inaccuracy",              // components/BeeIssue.vue
        relChild0: "Child Company",                         // components/BeeIssue.vue
        relParent0: "Parent Company",                       // components/BeeIssue.vue
        relChild1: "Investment",                            // components/BeeIssue.vue
        relParent1: "Investor",                             // components/BeeIssue.vue
        rel2: "Business Relationship",                      // components/BeeIssue.vue
        relMisc0: "Related Company",                        // components/BeeIssue.vue
        relMisc1: "Related through Investments",            // components/BeeIssue.vue
        relMisc2: "Related through Business Relationships", // components/BeeIssue.vue
        relChild0Of: "Owned by",                            // views/CompanyInfoPage.vue
        relParent0Of: "Parent of",                          // views/CompanyInfoPage.vue
        relChild1Of: "Invested in by",                      // views/CompanyInfoPage.vue
        relParent1Of: "Investing in",                       // views/CompanyInfoPage.vue
        rel2Of: "Business relationship with",               // views/CompanyInfoPage.vue
        searchBoxText: "Search for a company or brand.",    // components/CompanyAutocomplete.vue
        bigGraphLoader: "This may take a moment to load..." // components/GraphDisplay.vue
    }
};
/* Not currently covered
    components/BeeIssue.vue             -> "Ongoing since #date#"
    components/FeedbackForm.vue         -> all of it
    components/PotentiallyBigList.vue   -> "and #n# others"
    components/SaveButton.vue           -> text / "Save"
    views/*
*/
// {{$t("")}}