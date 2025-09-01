export default function search({
  recentSearchesKey,
  favoriteItemsKey,
  recentViewsKey,
  maxItemsAllowed,
  retainRecentIfFavorite
}) {
  return {
    recent_items: [], // recently views items
    favorite_items: [], // favorites
    recent_searches: [], // recent searches

    init: function () {
      this.recent_searches = this.getInitialItems(recentSearchesKey);
      this.recent_items = this.getInitialItems(recentViewsKey);
      this.favorite_items = this.getInitialItems(favoriteItemsKey);

      this.$watch("recent_searches", (vals) => {
        this.updateLocalStorage(recentSearchesKey, vals);
      });
      this.$watch("recent_items", (vals) => {
        this.updateLocalStorage(recentViewsKey, vals);
      });
      this.$watch("favorite_items", (vals) => {
        this.updateLocalStorage(favoriteItemsKey, vals);
      });
    },
    getInitialItems: function (key) {
      return JSON.parse(localStorage.getItem(key)) || [];
    },
    updateLocalStorage: function (key, vals) {
      localStorage.setItem(String(key), JSON.stringify(vals));
    },

    addToRecentItems: function (searchItem, group, url) {
      const searchItemObject = { item: searchItem, group, url };
      let history_data = this.recent_items.filter(
        (el) =>
          !(
            el.item === searchItemObject.item &&
            el.group === searchItemObject.group
          )
      );

      history_data = [searchItemObject, ...history_data].slice(
        0,
        maxItemsAllowed
      );

      this.recent_items = history_data;
    },

    deleteFromRecentItems: function (searchItem, group) {
      let index = this.recent_items.findIndex(
        (el) => el.item === searchItem && el.group === group
      );
      if (index !== -1) {
        this.recent_items.splice(index, 1);
      }
    },

    deleteAllRecentItems: function () {
      this.recent_items = [];
    },

    addToFavorites: function (favItem, group, url) {
      if(!retainRecentIfFavorite){
        this.deleteFromRecentItems(favItem,group);
      }
      const favItemObject = { item: favItem, group, url };
      let favorite_items = this.favorite_items.filter(
        (el) =>
          !(el.item === favItemObject.item && el.group === favItemObject.group)
      );
      favorite_items = [favItemObject, ...favorite_items].slice(
        0,
        maxItemsAllowed
      );
      this.favorite_items = favorite_items;
    },

    deleteFromFavorites: function (favItemToDelete, group) {
      let index = this.favorite_items.findIndex(
        (el) => el.item === favItemToDelete && el.group === group
      );
      if (index !== -1) {
        this.favorite_items.splice(index, 1);
      }
    },

    deleteAllFavorites: function () {
      this.favorite_items = [];
    },

    addToRecentSearches: function (item) {
      if (!item || typeof item.trim !== 'function' || item.trim() === '') {
        return;
      }
      const trimmedItem = item.trim();
      let history_data = this.recent_searches.filter(
        (i) => i.toLowerCase() !== trimmedItem.toLowerCase()
      );
      history_data = [item, ...history_data].slice(
        0,
        maxItemsAllowed
      );

      this.recent_searches = history_data;
    },

    deleteFromRecentSearches: function (item) {
      if (!item || typeof item.trim !== 'function' || item.trim() === '') {
        return;
      }

      let index = this.recent_searches.findIndex(
        (i) => i.toLowerCase() === item.toLowerCase()
      );
      if (index !== -1) {
        this.recent_searches.splice(index, 1);
      }
    },

    deleteAllRecentSearches: function () {
      this.recent_searches = [];
    },
  };
}
