import InfoPage from "src/views/Info/InfoPage";
import InfoPageAbout from "src/views/Info/InfoPageAbout";
import InfoPagePrivacy from "src/views/Info/InfoPagePrivacy";
import InfoPageFaq from "src/views/Info/InfoPageFaq";
import InfoPageFeedback from "src/views/Info/InfoPageFeedback";
import InfoPageCredits from "src/views/Info/InfoPageCredits";
export default { 
    path: "/info", component: InfoPage,
    children: [
        {
            path: "about",
            component: InfoPageAbout,
            meta: {
                title: "Sonic - About"
            }
        },
        { 
            path: "privacy",
            component: InfoPagePrivacy,
            meta: {
                title: "Sonic - Privacy Policy"
            }
        },
        {
            path: "faq",
            component: InfoPageFaq,
            meta: {
                title: "Sonic - Frequently Asked Questions"
            }
        },
        {
            path: "feedback",
            component: InfoPageFeedback,
            meta: {
                title: "Sonic - Feedback"
            }
        },
        {
            path: "credits",
            component: InfoPageCredits,
            meta: {
                title: "Sonic - Credits"
            }
        }
    ],
};