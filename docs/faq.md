---
title: FAQ
nav_order: 6
description: "Short answers about darvis/lemmings: the routes it adds, the path and the link, clearing the cache without SSH, the token and what the page reveals."
faq: true
---

# Frequently asked questions

{% for item in site.data.faq %}
## {{ item.q }}

{{ item.a | markdownify }}
{% endfor %}
