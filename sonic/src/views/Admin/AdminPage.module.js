import AdminPage from "src/views/Admin/AdminPage";
import AdminPageCompanyEdit from "src/views/Admin/AdminPageCompanyEdit";
import AdminPageCompanyList from "src/views/Admin/AdminPageCompanyList";
import AdminPageIssueTypeList from "src/views/Admin/AdminPageIssueTypeList";
import AdminPageCategoryList from "src/views/Admin/AdminPageCategoryList";
import AdminPageCategoryGraph from "src/views/Admin/AdminPageCategoryGraph";
import AdminPageFeedbackList from "src/views/Admin/AdminPageFeedbackList";
export default { 
    path: "/admin", component: AdminPage,
    children: [
        {
            path: "company/:id",
            component: AdminPageCompanyEdit,
            meta: {
                title: "SonicAdmin - Company Editor"
            }
        },
        {
            path: "company", 
            component: AdminPageCompanyList,
            meta: {
                title: "SonicAdmin - Manage Companies"
            }
        },
        {
            path: "issuetype", 
            component: AdminPageIssueTypeList,
            meta: {
                title: "SonicAdmin - Manage Issue Types"
            }
        },
        {
            path: "category", 
            component: AdminPageCategoryList,
            meta: {
                title: "SonicAdmin - Manage Categories"
            }
        },
        {
            path: "categorygraph", 
            component: AdminPageCategoryGraph,
            meta: {
                title: "SonicAdmin - Category Graph"
            }
        },
        {
            path: "feedback", 
            component: AdminPageFeedbackList,
            meta: {
                title: "SonicAdmin - Manage Feedback"
            }
        }
    ],
};