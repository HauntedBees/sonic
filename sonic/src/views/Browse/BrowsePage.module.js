import BrowsePage from "src/views/Browse/BrowsePage";
import BrowsePageCategories from "src/views/Browse/BrowsePageCategories";
import BrowsePageIssues from "src/views/Browse/BrowsePageIssues";
import BrowsePageGraph from "src/views/Browse/BrowsePageGraph";
import BrowsePageCompanies from "src/views/Browse/BrowsePageCompanies";
export default { 
    path: "/browse", component: BrowsePage,
    children: [
        {
            path: "issues",
            component: BrowsePageIssues,
            meta: {
                title: "Sonic - Browse by Issue"
            }
        },
        {
            path: "graph",
            component: BrowsePageGraph,
            meta: {
                title: "Sonic - Company Graph"
            }
        },
        {
            path: "categories",
            component: BrowsePageCategories,
            meta: {
                title: "Sonic - Browse by Category"
            }
        },
        {
            path: "companies",
            component: BrowsePageCompanies,
            meta: {
                title: "Sonic - Browse by Company"
            }
        }
    ],
};