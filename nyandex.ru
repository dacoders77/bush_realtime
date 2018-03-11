[33mcommit 51c9f44496ba33b9261559d5e4cedc1c60b17374[m[33m ([m[1;36mHEAD -> [m[1;32mmaster[m[33m)[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Sun Mar 11 15:59:34 2018 +0300

    Trade profit calculation added

[33mcommit 3f3258c9f73998e1fc818d5e31855519beb68978[m[33m ([m[1;31morigin/master[m[33m)[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Sat Mar 10 18:34:23 2018 +0300

    Trade commission, accumulated commission added. Tested

[33mcommit 102c90bc1ae576b4d4af31a35ee58d06977164c5[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Thu Mar 8 15:37:00 2018 +0300

    Trades are ready and tested in live server. Long, short, opposite

[33mcommit e42eaacce1fa2abf7faa0865521f5af56d840223[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Thu Mar 8 12:25:38 2018 +0300

    PlaceOrder method is ready and tested. Can go long, short. Buttons to UI added

[33mcommit 8807ff91d536869e8b4a89e7c82908c43415d368[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Thu Mar 8 10:09:53 2018 +0300

    Asstet changet to ETHUSD, price channel=1, timeframe=1

[33mcommit f75bd3adc20de83d8a92c78266c5493c71c278f1[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Tue Mar 6 12:00:29 2018 +0300

    Wrong current bar add fixed, trades are executed on bar close not on price channel touch. Ready for open_order

[33mcommit 98737032803793cdb297234428a6efc4ae0918d9[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Tue Mar 6 00:43:43 2018 +0300

    Trades marks are ready, allow_trading flag added to migration

[33mcommit 97c46499889f26411e2ae1aeb3803c8894057736[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Mon Mar 5 00:08:31 2018 +0300

    Price channel value pulled from DB fixed, migration column added

[33mcommit 667ef2b4bd0a7acb2557330e293a4f46fbd7a860[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Sun Mar 4 22:52:02 2018 +0300

    Ready realtime chart: bars, price channel, history load. Ratchet: tracks high, low price channel and ready for trades

[33mcommit eb91cc7dabda75e0a73c20ada849cb40b6d1ac59[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Sun Mar 4 11:05:19 2018 +0300

    Price channel fixed but not realtime yet

[33mcommit 021208e6da15391dc06813730d292a14ceb9d781[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Sat Mar 3 19:18:39 2018 +0300

    Added controllers which were missing at the server

[33mcommit 326ef1d224e4be5bb2f270863eef87a4dfb198da[m
Merge: 1d3c124 3694d8b
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Sat Mar 3 17:56:03 2018 +0300

    Fixed merge conflicts2

[33mcommit 1d3c12445bc5036a1683115874d995dba6eaec33[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Sat Mar 3 15:24:04 2018 +0300

    Bars are added in realtime time frame 1m

[33mcommit 8ce8c0f3522163bf57f032962b55d688ce0c2c23[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Fri Mar 2 01:07:05 2018 +0300

    Realtime bar added to the static chart. Migration, history load, output data to the chart

[33mcommit 5b35391639191e976d47b8e4f1b6c6c083e2ad1b[m
Author: Boris Borisov <djslinger77@gmail.com>
Date:   Tue Feb 27 00:57:15 2018 +0300

    Realtime chart added with websocket events
