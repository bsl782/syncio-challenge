<script setup>
import { defineProps } from 'vue'

const props = defineProps({
    data: Object, // Object or Array to Render
    diffs: Object, // Structured Diff uses it as a blueprint to highlight payloads
    isSecond: Boolean, // If true it renders the 'after' version for diff highlighting
    level: Number, // Indent space levels

})

// Determines the inline style used to highlight a particular item based on the diff
const getHighlightStyle = (diff, val) => {
    if (!diff) {
        return {}
    }
    // Highlight changed values
    if (diff.type === 'changed') {
        const match = props.isSecond ? val === diff.to : val === diff.from
        if (match) {
            return { backgroundColor: '#FEF3C7' };
        }
        else {
            return {};
        }
    }
    // Highlight added values
    if (diff.type === 'added' && props.isSecond) {
        return { backgroundColor: '#DCFCE7' }
    }
    // Highlight removed values
    if (diff.type === 'removed' && !props.isSecond) {
        return { backgroundColor: '#FEE2E2', textDecoration: 'line-through' }
    }
}

const spacingLeft = () => {
    return { paddingLeft: `${props.level * 15}px` }
}

</script>

<template>
    <div class="whitespace-pre-wrap text-left">


        <!-- Array Handling -->
        <template v-if="Array.isArray(data)">
            <div :style="spacingLeft()">[</div>

            <div v-for="(item, index) in data" :key="index" :style="getHighlightStyle(diffs?.[index], item)">
                <div :style="spacingLeft()">
                    <!-- Nested array recursive -->
                    <template v-if="typeof item === 'object' && item !== null">
                        <PayloadView :data="item" :diffs="diffs?.[index]?.children || {}" :isSecond="isSecond"
                            :level="level+=1" />
                    </template>
                    <!-- Handles primitive values -->
                    <template v-else>
                        <div :style="spacingLeft()">
                            {{ JSON.stringify(item) }}
                        </div>
                    </template>
                </div>
            </div>
            <div :style="spacingLeft()">]</div>
        </template>


        <!-- Object Handling -->
        <template v-else>
            <div :style="spacingLeft()">{</div>
                
            <div v-for="(val, key) in data" :key="key" :style="getHighlightStyle(diffs?.[key], val)">
                <div :style="spacingLeft()">
                    <span :style="spacingLeft()">"{{ key }}"</span>:
                    <!-- Nested object or array -->
                    <template v-if="typeof val === 'object' && val !== null">
                        <PayloadView :data="val" :diffs="diffs?.[key]?.children || {}" :isSecond="isSecond"
                            :level="level+=1" />
                    </template>
                    <!-- Handles primitive values -->
                    <template v-else>
                        <span>
                            {{ JSON.stringify(val) }}
                        </span>
                    </template>
                </div>
            </div>
            <div :style="spacingLeft()">}</div>
        </template>
    </div>
</template>
