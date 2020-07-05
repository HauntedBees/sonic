<template>
    <v-container>
        <v-navigation-drawer class="d-none d-sm-flex" color="#F90018" absolute permanent dark>
            <v-list dense nav>
                <v-list-item-group>
                    <v-list-item v-for="(item, i) in items" link :key="i" :to="item.href">
                        <v-list-item-icon>
                            <v-icon>{{item.icon}}</v-icon>
                        </v-list-item-icon>
                        <v-list-item-content>
                            <v-list-item-title>{{item.title}}</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>
        <div class="d-block d-sm-none mobileNav">
            <v-menu top offset-y>
                <template v-slot:activator="{ on, attrs }">
                    <v-btn color="#F90018" dark v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-horizontal</v-icon>
                    </v-btn>
                </template>
                <v-list>
                    <v-list-item v-for="(item, i) in items" link :key="i" :to="item.href">
                        <v-list-item-title>{{item.title}}</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
        </div>
    </v-container>
</template>
<script>
    export default {
        props: {
            "items": { type: Array }
        }
    }
</script>