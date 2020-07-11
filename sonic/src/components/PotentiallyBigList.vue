<template>
    <div>
        <BeeSubheader :text="title" />
        <div class="beesubmessage" v-if="items.length < 4 || showAll">
            <span v-for="(item, key) in items" :key="key">
                <span v-if="key !== 0">, </span>
                <router-link :to="`/`+item.text">{{item.text}}</router-link>
            </span>
        </div>
        <div class="beesubmessage" v-if="items.length >= 4 && !showAll">
            <span v-for="(item, key) in compChildren" :key="key">
                <span v-if="key !== 0">, </span>
                <router-link :to="`/`+item.text">{{item.text}}</router-link>
            </span>,
            <span class="beelink" @click="showAll=true">and {{items.length - 2}} others</span>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            "title": { type:String, required: true },
            "items": { type:Array, required: true }
        },
        data: () => ({
            showAll: false
        }),
        computed: {
            compChildren() { return this.items.slice(0, 2); }
        }
    }
</script>